<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2020 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

/**
 * Class ModelToolMPAPI  - class for converting parameters from url to api-request for marketplace
 *
 */
class ModelToolMPAPI extends Model
{
    protected $mp_url = 'aHR0cHM6Ly9tYXJrZXRwbGFjZS5hYmFudGVjYXJ0LmNvbS8=';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    /**
     * @return string
     */
    public function getMPURL()
    {
        return base64_decode($this->mp_url);
    }

    /**
     * @return array
     * @throws AException
     */
    public function getPopularity()
    {
        $url = $this->getMPURL() . "popular.php";
        //silent request
        $connect = new AConnect(true);
        $this->load->library('json');
        return AJson::decode($connect->getResponse($url), true) ?: [];
    }

    /**
     * @param string $mp_token
     *
     * @return bool
     * Disconnect store from AbanteCart marketplace
     * @throws AException
     */
    public function disconnect($mp_token)
    {
        if (!has_value($mp_token)) {
            return false;
        }

        $params = [
            'rt'       => 'a/account/authorize/disconnect',
            'mp_token' => $mp_token,
        ];
        $connect = new AConnect();
        $connect->connect_method = 'curl';
        return $this->send($connect, $params);
    }

    /**
     * Standalone API call to validate token authentication
     *
     * @param string $mp_token
     *
     * @return bool
     * @throws AException
     */
    public function authorize($mp_token)
    {
        if (!$mp_token) {
            return false;
        }

        $auth_params = [
            'rt'       => 'a/account/authorize/authorize',
            'mp_token' => $mp_token,
        ];

        $connect = new AConnect();
        $connect->connect_method = 'curl';
        $auth = $this->send($connect, $auth_params);

        if ($auth['status']) {
            //remove cache info about updates
            $this->cache->remove('extensions');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Standalone API call to get purchased extensions
     *
     * @param string $mp_token
     *
     * @return array
     * @throws AException
     */
    public function getMyExtensions($mp_token)
    {
        if (!$mp_token) {
            return [];
        }

        $connect = new AConnect(true);
        $connect->connect_method = 'curl';
        $params = [
            'rt'       => 'a/account/account/get_extensions',
            'mp_token' => $mp_token,
        ];
        return $this->send($connect, $params);
    }

    public function processRequest($params = [])
    {
        $output = [
            'categories' => [],
            'products'   => [],
        ];
        $connect = new AConnect();
        $connect->connect_method = 'curl'; // set curl as default connection type

        // prepare parameters
        if (has_value($params['limit'])) {
            $get_params['limit'] = $get_params['rows'] = $params['limit'];
        } else {
            $get_params['limit'] = $get_params['rows'] = 24;
        }
        if (has_value($params['page'])) {
            $get_params['page'] = $params['page'];
        }
        if (has_value($params['sidx'])) {
            $get_params['sidx'] = $params['sidx'];
        } else {
            $get_params['sidx'] = 'rating';
        }
        if (has_value($params['sord'])) {
            $get_params['sord'] = $params['sord'];
        } else {
            $get_params['sord'] = 'DESC';
        }
        //pass token if have it
        if ($params['mp_token']) {
            $get_params['mp_token'] = $params['mp_token'];
        }
        // get category list
        $output['categories'] = $this->send(
            $connect,
            [
                'rt'          => 'a/product/category',
                'category_id' => 0,
            ]
        );

        if ($output['categories']) {
            foreach ($output['categories']['subcategories'] as &$category) {
                $category['href'] = $this->html->getSecureURL(
                    'extension/extensions_store',
                    '&category_id='.$category['category_id']
                    .'&sidx='.$get_params['sidx']
                    .'&sord='.$get_params['sord']
                    .'&limit='.$get_params['limit']
                );
                $category['active'] = ($category['category_id'] == $params['category_id']);
            }
            unset($category);
            //add all categories option at the beginning of array
            array_unshift(
                $output['categories']['subcategories'],
                [
                    'category_id' => '',
                    'name'        => $this->language->get('text_all_categories'),
                    'href'        => $this->html->getSecureURL(
                                                              'extension/extensions_store',
                                                              '&sidx='.$get_params['sidx']
                                                              .'&sord='.$get_params['sord']
                                                              .'&limit='.$get_params['limit']
                                                            ),
                    'active'      => $params['category_id'] ? false : true,
                ]
            );
        }
        //Load purchased extensions if requested
        if ($params['purchased_only']) {
            $get_params['purchased_only'] = $params['purchased_only'];
            $get_params['rt'] = 'a/product/filter';
            $output['products'] = $this->send($connect, $get_params);
        } elseif (has_value($params['category_id'])) {
            // get products of category
            $get_params['rt'] = 'a/product/filter';
            $get_params['category_id'] = (int) $params['category_id'];
            $output['products'] = $this->send($connect, $get_params);
        } elseif (has_value($params['keyword'])) {//get products by keyword
            $get_params['rt'] = 'a/product/filter';
            $get_params['keyword'] = $params['keyword'];
            $output['products'] = $this->send($connect, $get_params);
        } else {
            //default latest listing
            $get_params['rt'] = 'a/product/filter';
            $output['products'] = $this->send($connect, $get_params);
        }

        //prepare extensions for listing
        //Check if extension is installed or requires updating based on versions

        if ((array) $output['products'] && (array) $output['products']['rows']) {
            foreach ($output['products']['rows'] as &$product) {
                $info = $product['cell'];
                $info['rating'] = (int) $info['rating'];
                $info['description'] = substr(
                        strip_tags(
                            html_entity_decode(
                                str_replace('&nbsp;', '', $info['description']),
                                ENT_QUOTES
                            )
                        ),
                        0,
                        344
                    ).'...';

                //do not use currency class for format, as it can be manipulated by user
                if ($info['price'] > 0) {
                    $info['price'] = "$".number_format($info['price'], 2, '.', ',');
                } else {
                    $info['price'] = $this->language->get('text_free');
                }

                $product['cell'] = $info;
            }
        }

        if (!$output['categories'] && !$output['products']) {
            $output = [];
        }
        return $output;
    }

    /**
     * @param AConnect $connect
     * @param array $params - plain associative array
     *
     * @return mixed
     * @throws AException
     */
    private function send($connect, $params = [])
    {
        if (!is_object($connect)) {
            return false;
        }

        $GET['store_id'] = UNIQUE_ID;
        $GET['store_url'] = HTTP_SERVER;
        $GET['store_version'] = VERSION;
        $GET['language_code'] = $this->request->cookie ['language'];

        // place your affiliate id here
        define('MP_AFFILIATE_ID', '');
        if (MP_AFFILIATE_ID) {
            $GET['aff_id'] = MP_AFFILIATE_ID;
        }

        $GET = array_merge($params, $GET);
        $href = '?'.http_build_query($GET);
        return $connect->getResponse($this->getMPURL().$href);
    }
}

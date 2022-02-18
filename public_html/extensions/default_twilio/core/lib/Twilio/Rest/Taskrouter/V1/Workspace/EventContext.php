<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Taskrouter\V1\Workspace;

use Twilio\InstanceContext;
use Twilio\Values;
use Twilio\Version;

class EventContext extends InstanceContext
{
    /**
     * Initialize the EventContext
     *
     * @param \Twilio\Version $version      Version that contains the resource
     * @param string          $workspaceSid The workspace_sid
     * @param string          $sid          The sid
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\EventContext
     */
    public function __construct(Version $version, $workspaceSid, $sid)
    {
        parent::__construct($version);

        // Path Solution
        $this->solution = array('workspaceSid' => $workspaceSid, 'sid' => $sid,);

        $this->uri = '/Workspaces/'.rawurlencode($workspaceSid).'/Events/'.rawurlencode($sid).'';
    }

    /**
     * Fetch a EventInstance
     *
     * @return EventInstance Fetched EventInstance
     */
    public function fetch()
    {
        $params = Values::of(array());

        $payload = $this->version->fetch(
            'GET',
            $this->uri,
            $params
        );

        return new EventInstance(
            $this->version,
            $payload,
            $this->solution['workspaceSid'],
            $this->solution['sid']
        );
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString()
    {
        $context = array();
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Taskrouter.V1.EventContext '.implode(' ', $context).']';
    }
}
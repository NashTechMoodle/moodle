<?php
/**
 *
 */

class backpacklib {

    private $backpackoauth2 = new \stdClass();
    private $backpackapi = new \stdClass();
    public function __construct(core_badges\lib\backpack\backpack_oauth2 $backpackoauth2, $user) {
        $this->backpackoauth2 = $backpack;
        $this->backpack = badges_get_site_backpack($backpackoauth2->get('externalbackpackid'));
        $this->backpackapi = new core_badges\backpack_api($backpack);
    }

    public function check_token() {
        // Check token.
    }

    public function api($key, $method, $data) {
        //Check token and call api
        if($this->check_token()) {
            if($key == 'assertions' && $method == 'post') {
                $responsive = $this->backpackapi->post_assertions($data);
            } else if($key == 'assertions' && $method == 'get') {
                $responsive = $this->backpackapi->get_assertions($data);
            } else if($key == 'profile' && $method == 'post') {
                $responsive = $this->backpackapi->post_profile($data);
            } else if($key == 'profile' && $method == 'get') {
                $responsive = $this->backpackapi->get_profile($data);
            }
        }


        foreach ($responsive as $assertion) {
            badges_external_create_mapping(
                $this->backpack->id, OPEN_BADGES_V2_TYPE_ASSERTION, $assertion->assertionid, $assertion->id);
        }
    }

    // Delete, update
}


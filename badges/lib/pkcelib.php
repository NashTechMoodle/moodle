<?php
/*
 * For development.
 */

/**
 * PKCE library.
 */

defined('MOODLE_INTERNAL') || die();

class pkcelib {

    function base64URLEncode(str) {
        return str.toString('base64')
            .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
    }
}
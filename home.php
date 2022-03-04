<?php

// We want to redirect users to the index page after login,
// but if we do so directly they don't appear logged in.
//
// This is because the index page, by design, doesn't try to authenticate users
// (since it must also work for unauthenticated ones).
//
// So we created this page as a "trampoline", it logs in users,
// creates their session cookies and then sends them to the home page.

// Force the logging in of users
require_once 'secure.inc.php';

// Redirect to home page
Http::redirect(ROOT_PATH . 'index.php');

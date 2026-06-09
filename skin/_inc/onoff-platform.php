<?php
/**
 * 온오프 플랫폼 스킨 공통 헤더
 * @onoff-platform-managed
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_platform_member_styles')) {
    function onoff_platform_member_styles($skin_url = '')
    {
        if (!function_exists('add_stylesheet')) {
            return;
        }

        $tokens = G5_URL . '/css/icrm-design-tokens.css';
        $platform = G5_URL . '/css/onoff-platform.css';
        add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($tokens, ENT_QUOTES, 'UTF-8') . '">', 0);
        add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($platform, ENT_QUOTES, 'UTF-8') . '">', 1);

        if ($skin_url !== '') {
            add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($skin_url, ENT_QUOTES, 'UTF-8') . '/style.css">', 2);
        }
    }
}

if (!function_exists('onoff_platform_board_styles')) {
    function onoff_platform_board_styles($board_skin_url = '')
    {
        if (!function_exists('add_stylesheet')) {
            return;
        }

        $tokens = G5_URL . '/css/icrm-design-tokens.css';
        $platform = G5_URL . '/css/onoff-platform.css';
        add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($tokens, ENT_QUOTES, 'UTF-8') . '">', 0);
        add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($platform, ENT_QUOTES, 'UTF-8') . '">', 1);

        if ($board_skin_url !== '') {
            add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($board_skin_url, ENT_QUOTES, 'UTF-8') . '/style.css">', 2);
        }
    }
}

if (!function_exists('onoff_platform_outlogin_styles')) {
    function onoff_platform_outlogin_styles($outlogin_skin_url = '')
    {
        if (!function_exists('add_stylesheet')) {
            return;
        }

        $tokens = G5_URL . '/css/icrm-design-tokens.css';
        $platform = G5_URL . '/css/onoff-platform.css';
        add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($tokens, ENT_QUOTES, 'UTF-8') . '">', 0);
        add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($platform, ENT_QUOTES, 'UTF-8') . '">', 1);

        if ($outlogin_skin_url !== '') {
            add_stylesheet('<link rel="stylesheet" href="' . htmlspecialchars($outlogin_skin_url, ENT_QUOTES, 'UTF-8') . '/style.css">', 2);
        }
    }
}

<?php

//$baseurl = getHost();
$baseurl = getHost();

return [
    'ADMIN_URL'         => $baseurl,
    'ADMIN_LOGO'        => $baseurl . 'public/asset/images/common/admin-logo.png',
    'SITE_NAME'         => env('APP_NAME'),
    'SITE_TITLE_ENG'    => env('APP_NAME'),
    'SITE_TITLE_OTHER'  => env('APP_NAME'),
    'BASE_URL'          => $baseurl,
    'PDF_PATH'          => $baseurl . 'public/asset/documents/pdf/',
    'IMG_PATH'          => $baseurl . 'public/asset/images/',
    'COMMON_IMG_PATH'   => $baseurl . 'public/asset/images/common/',
    'JS_PATH'           => $baseurl . 'public/asset/js/landingpage/',
    'CSS_PATH'          => $baseurl . 'public/asset/css/landingpage/',
    'IMAGE_PATH'        => $baseurl . 'public/asset/css/landingpage/',
    'FONT_PATH'         => $baseurl . 'public/asset/css/fonts/',
    'PLUGIN_PATH'       => $baseurl . 'public/asset/plugins/',
    'UPLOAD_PATH'       => $baseurl . 'public/asset/uploads/',
    'DEVELOPMENT_IP'    => array('', '', ''),
    'ADMIN_JS_PATH'     => $baseurl . 'public/asset/js/docsadmin/',
    'ADMIN_CSS_PATH'    => $baseurl . 'public/asset/css/docsadmin/',
    'ADMIN_IMG_PATH'    => $baseurl . 'public/asset/images/docsadmin/',
    'EXTLINKICON'       => 'ext-link-icon.gif',
    'PDFICON'           => 'PDF-icon.gif',
    'NEWICON'           => 'new.gif',
    'VISITORS_COUNT'    => 0,

];

<?php
// Prevent directory listing
// This file does nothing but prevents directory browsing
http_response_code(403);
die('Access Forbidden');
?>
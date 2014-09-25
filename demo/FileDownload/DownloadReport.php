<?php

$id = intval($_REQUEST['id']);

if ($id % 2 === 0)
{
    // For performance testing: try a 500+ MB PDF first (as that surely will exhaust RAM for any 'naive' PHP copy implementations):
    $file_name = './Digital Design - Principles And Practices 4th Edition by John F Wakerly.pdf';
    if (!file_exists($file_name))
    {
        $file_name = '../../src/Report.pdf';
    }

    // As per:
    //     http://stackoverflow.com/questions/3697748/fastest-way-to-serve-a-file-using-php
    if (file_exists($file_name))
    {
        // // See for this way to get the MIME type: http://php.net/manual/en/function.virtual.php
        // $file_info = apache_lookup_uri($file_name);
        // header('content-type: ' . $file_info->content_type);

        // !!!Note!!!
        // Set cookie to mark download commencing:
        setcookie('fileDownload', 'true', 0, '/', false, !empty($_SERVER["HTTPS"]));         // lifetime: until end of session

        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');

        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // As per:
        //     http://stackoverflow.com/questions/12233386/php-how-to-find-out-if-x-sendfile-is-available-and-installed
        //     http://stackoverflow.com/questions/4022260/how-to-detect-x-accel-redirect-nginx-x-sendfile-apache-support-in-php
        //     http://www.gravitywell.co.uk/latest/how-to/posts/securing-your-downloads-with-php-and-mod-xsendfile/
        //     https://tn123.org/mod_xsendfile/
        //     http://codeutopia.net/blog/2009/03/06/sending-files-better-apache-mod_xsendfile-and-php/
        if (function_exists('apache_get_modules') && in_array('mod_xsendfile', apache_get_modules()) && $_SERVER['MOD_X_SENDFILE_ENABLED'])
        {
            header("X-Sendfile: $file_name");
        }
        else
        {
            error_log("Warning! mod-xsendfile is NOT INSTALLED - sending file the old fashion way.....");
            // http://stackoverflow.com/questions/20095175/php-readfile-vs-file-get-contents
            // http://php.net/manual/en/function.readfile.php
            header('Content-Length: ' . filesize($file_name));
            // http://php.net/manual/en/function.virtual.php
            apache_setenv('PHP_ALLOW', '1');
            //
            // Note: Performance test: as mentioned elsewhere on the web, virtual() is the fastest kid on the block, matching 'static file' native server speeds. :-)
            // 
            // @readfile($file_name);
            if (virtual($file_name))
            {
                exit(0);
            }
            echo "Oops, failed to fetch file $file_name (hammer your webmaster).\n";
        }
        exit(0);
    }
    // else: fail!
    setcookie('fileDownloadFailureReason', "File $file_name does not exist.", 0, '/', false, !empty($_SERVER["HTTPS"]));         // lifetime: until end of session
    http_response_code(404);
    echo "<html><body><h1>bugger!</h1><p>File $file_name does not exist!</p></body></html>";
    die();
}
else
{
    // fail:
    setcookie('fileDownloadFailureReason', "Odd-request-ID intentionally induced error @ ID = $id.", 0, '/', false, !empty($_SERVER["HTTPS"]));         // lifetime: until end of session
    http_response_code(560 + ($id % 40));
    echo "<html><body><h1>bugger!</h1><p>Odd-request-ID intentionally induced error @ ID = $id</p></body></html>";
    die();
}

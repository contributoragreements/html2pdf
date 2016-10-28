<?php
    #
    # To use this, you need to post to this as a url, with post input named
    # 'htmlstore' and for titling the file, use 'title'
    #
    # NOTE: You need a custom compiled version of wkhtmltodpdf with qt that
    # does not need X server, so statically compiled is the best
    $html2pdf = '/app/bin/wkhtmltopdf';

    $getdate = date('Y-m-d-H_i_s');
    $gethash = md5(date('Ymdgisu'));

    $doDebug = false;

    $error_message = '<h4>Your PDF file could not be generated. Please contact <a href="mailto:support@fabricatorz.com">support@fabricatorz.com</a>.</h4>';

    $html = '';
    // if htmlstore exists, get it, if need more than one type, then
    // only get one of the types sent over
    if ( !empty($_REQUEST['htmlstore']) )
        $html       = $_REQUEST['htmlstore'];
    elseif ( !empty($_REQUEST['type']) && 
             isset($_REQUEST['htmlstore-' . $_REQUEST['type']]) )
    {
        $html = $_REQUEST['htmlstore-' . $_REQUEST['type']];
    }
    $filename   = make_seo_url($_REQUEST['title']) . "-" . $getdate;

    $file_html = "/tmp/" . $filename . ".html";
    $file_pdf  = "/tmp/" . $filename . ".pdf";
 
    if ( $doDebug )
    {
        echo "<pre>";
        var_dump($_REQUEST);
        var_dump($html);
        echo "</pre>";
        echo "<p>$file_html</p>";
        echo "<p>$file_pdf</p>";
        echo $title;
        echo $html;
        exit;
    }

    if ( empty($html) || ( FALSE !== file_put_contents($file_html, $html) ) )
    {
        $cmd = escapeshellcmd("$html2pdf '$file_html' '$file_pdf'");
        exec($cmd);

        if (file_exists($file_pdf))
        {
            $filename  = $filename . ".pdf";
            $file_size = filesize($file_pdf);

            // Output headers.
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=".$filename);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header("Content-Length: ".$file_size);
            // Output file.
            ob_clean();
            flush();
            readfile ($file_pdf);
            exit();
        }
        else {
            die($error_message);
        }


    } else {
        echo $error_message;
    }


    function make_seo_url ($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\.\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

?>

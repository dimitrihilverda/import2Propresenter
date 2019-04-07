<?php

if ($_POST['text'] != '') {

    $title = $_POST['title'];
    $text  = strtoupper($_POST['text']);
    $outer = file_get_contents('base.xml');
    $outer = str_replace('[[+title]]',$title, $outer);

    $textArray = preg_split( '/\r\n|\r|\n/', $text);
    $textArray = array_filter($textArray);

    $second = false;
    $pair   = [];
    foreach ($textArray as $item) {
        if (trim($item) == '') {
            continue; 
        }
        $pair[] = $item;

        // Eerste loop is item 1
        if (!$second) {
            $second = true;
            continue;
        } else {
            $second = false;

            // Hier bevat $pair 2 items
            $slides[] = $pair;

            $pair = [];
        }
    }

    foreach ($slides as $slide) {
        $flowdocline       = [];
        $flowdocbase64line = [];
        $second            = false;
        foreach ($slide as $line) {
            if (!$second) {
                $flowdoc = file_get_contents('flowdoc.xml');
                $second  = true;
            } else {
                $second  = false;
                $flowdoc = file_get_contents('flowdoc2.xml');
            }

            $lineBase64          = base64_encode($line);
            $flowdocxml          = str_replace('[[+line]]', $line, $flowdoc);
            $flowdocbase64line[] = base64_encode($flowdocxml);
            $flowdocline[]       = $line;
        }
        $slidexml           = file_get_contents('slide.xml');
        $replaceLineArray   = ['[[+line1]]', '[[+line2]]'];
        $replaceBase64Array = ['[[+flowdoc1]]', '[[+flowdoc2]]'];
        $slidexml           = str_replace($replaceLineArray, $lineBase64, $slidexml);
        $slideArray[]       = str_replace($replaceBase64Array, $flowdocbase64line, $slidexml);
    }
    $slidesAll = implode("\n", $slideArray);
    $output    = str_replace('[[+slides]]', $slidesAll, $outer);

    $length = strlen($output);

    header('Content-Description: File Transfer');
    header('Content-Type: text/plain');//<<<<
    header('Content-Disposition: attachment; filename='.$title.'.pro6');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.$length);
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    header('Pragma: public');

    echo $output;
    exit;
} else {
    $output = '<form action="" method="post">';
    $output .= ' Titel:<br />';
    $output .= '<input name="title" type="text" required><br />';
    $output .= 'Text:<br />';
    $output .= '<textarea name="text" cols="150" required rows="30"></textarea>';
    $output .= '<input type="submit" value="maak song">';
    $output .= '</form>';
    echo $output;
}
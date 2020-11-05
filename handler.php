<?php
    /**
     * Author: Simone Di Paolo
     * Company: Riequilibrium Web Agency
     * Contact: it@riequilibrium.com
     * Date: 2020-11-04
     * Description: Handles the download or elimination of a QR Code
     */
    if(isset($_POST["type"]) && isset($_POST["permalink"]) && isset($_POST["postID"])){
        if(!file_exists("./tmp/")) // If the directory "tmp" doesn't exists
            mkdir("./tmp/"); // Create the directory "tmp"
        $img = "./tmp/qr-post-" . $_POST["postID"] . ".png";
        if($_POST["type"] == "download"){ // If the request type is download
            $url = "https://chart.apis.google.com/chart?cht=qr&chs=500x500&chl=" . $_POST["permalink"];
            file_put_contents($img, file_get_contents($url)); // Saves in a temporary file the QR, taken from the URL request to the Google's API
        }else if($_POST["type"] == "delete") // If the request type is delete
            unlink($img); // Deletes the temporary image
    }
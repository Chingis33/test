<?php
class FTP {

    private $host;
    private $user;
    private $pass;
    private $conn;
    private $remoteFile;

    public function __construct($host, $user, $pass)
    {

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;

        $this->conn = ftp_connect($this->host) or die("Cant connect to $host");
        ftp_login($this->conn, $this->user, $this->pass);
    }

    function ftpCopyFile($remoteFile)
    {
        $this->remoteFile = $remoteFile;
        ftp_put($this->conn, $this->remoteFile, $this->remoteFile, FTP_ASCII);
    }
}

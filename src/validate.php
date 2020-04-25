<?php 
    if(define('isSet', 1)){
        die('<h1>Direct access is not allowed!</h1>');
    }

    class userChecking
    {
        public function __construct($username, $email, $password)
        {
            $this->username = $username;
            $this->email = $email;
            $this->password = $password;
        }
        
        public function checkUsername()
        {
            if ($this->username == '') {
                $GLOBALS['errUsername'] = '<b>Username must be fill out!</b>';
                return false;
            } elseif (strlen($this->username) > 50) {
                $GLOBALS['errUsername'] = '<b>Username must be less than 50 characters</b>';
                return false;
            } else {return true;}
        }
        
        public function checkEmail()
        {
            if ($this->email == '') {
                $GLOBALS['errEmail'] = '<b>Email must be fill out!</b>';
                return false;
            } elseif (strlen($this->email) > 50) {
                $GLOBALS['errEmail'] = '<b>Email must be less than 50 characters</b>';
                return false;
            } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $GLOBALS['errEmail'] = '<b>Invalid email</b>';
                return false;
            } else {return true;}
        }

        public function checkPassword()
        {
            if ($this->password == '') {
                $GLOBALS['errPassword'] = '<b>Password must be fill out!</b>';
                return false;
            } elseif (strlen($this->password) > 50) {
                $GLOBALS['errPassword'] = '<b>Password must be less than 50 characters</b>';
                return false;
            } else {return true;}
        }
        private function __createHashPW()
        {
            $hashed = password_hash($this->password, PASSWORD_BCRYPT);
            $this->hashed = $hashed;
        }
        public function getHashPW()
        {
            $this->__createHashPW();
            return $this->hashed;
        }
    }
?>
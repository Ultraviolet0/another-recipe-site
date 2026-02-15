<?php

class Session {

  private $user_id;
  public $username;
  private $last_login;
  private $roles = [];

  public const MAX_LOGIN_AGE = 60 * 60 * 24; // 1 day

  public function __construct() {
    session_start(); // turn on sessions if needed
    $this->check_stored_login();
  }

  public function login($user) {
    if($user) {
      // prevent session fixation attacks
      session_regenerate_id();

      $this->user_id = $_SESSION['user_id'] = $user->id_usr;
      $this->username = $_SESSION['username'] = $user->username_usr;
      $this->last_login = $_SESSION['last_login'] = time();

      $this->roles = $_SESSION['roles'] = $user->role_names();
    }
    return true;
  }

  public function is_logged_in() {
    return isset($this->user_id) && $this->last_login_is_recent();
  }

  public function is_admin_logged_in() {
    return $this->is_logged_in() && $this->is_admin();
  }

  public function is_super_admin_logged_in() {
    return $this->is_logged_in() && $this->is_super_admin();
  }

  private function is_admin() {
    if($this->has_role('admin') || $this->has_role('super admin')) {
      return true;
    } else {
      return false;
    }
  }

  private function is_super_admin() {
    if($this->has_role('super admin')) {
      return true;
    } else {
      return false;
    }
  }

  public function logout() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['last_login']);
    unset($_SESSION['roles']);
    unset($this->user_id);
    unset($this->username);
    unset($this->last_login);
    unset($this->roles);
    return true;
  }

  public function get_user_id() {
    return $this->user_id ?? null;
  }

  public function get_roles() {
    return $this->roles ?? [];
  }

  public function has_role($role_name) {
    $role_name = strtolower($role_name);
    $roles = array_map('strtolower', $this->get_roles());
    return in_array($role_name, $roles, true);
  }

  private function check_stored_login() {
    if(isset($_SESSION['user_id'])) {
      $this->user_id = $_SESSION['user_id'];
      $this->username = $_SESSION['username'];
      $this->last_login = $_SESSION['last_login'];
      $this->roles = $_SESSION['roles'] ?? [];
    }
  }

  private function last_login_is_recent() {
    if(!isset($this->last_login)) {
      return false;
    } elseif(($this->last_login + self::MAX_LOGIN_AGE) < time()) {
      return false;
    } else {
      return true;
    }
  }

  public function message($msg="") {
    if(!empty($msg)) {
      // Then this is a "set" message
      $_SESSION['message'] = $msg;
      return true;
    } else {
      // Then this is a "get" message
      return $_SESSION['message'] ?? '';
    }
  }

  public function clear_message() {
    unset($_SESSION['message']);
  }
}

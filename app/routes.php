<?php
$this->reg('', 'user@index');
$this->reg('login', 'user@login');
$this->reg('register', 'user@register');
$this->reg('logout', 'user@logout');

$this->reg('home', 'home@index');
$this->reg('create', 'game@create', 'POST');
$this->reg('manage', 'game@manage');
$this->reg('convert', 'home@convert', 'POST');
$this->reg('change', 'game@change', 'POST');

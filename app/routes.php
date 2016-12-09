<?php
$this->reg('', 'user@index');
$this->reg('login', 'user@login');
$this->reg('register', 'user@register');
$this->reg('logout', 'user@logout');

$this->reg('home', 'home@index');
$this->reg('create', 'game@create');
$this->reg('manage', 'game@manage');
$this->reg('convert', 'home@convert', ['POST', 'GET']);
$this->reg('change', 'game@change', 'POST');
$this->reg('gameChange', 'game@gameChange', 'POST');
$this->reg('changeMap', 'game@changeMap');
$this->reg('start', 'game@start', 'POST');
$this->reg('stop', 'game@stop', 'POST');
$this->reg('upgrade', 'game@upgrade');
$this->reg('delete', 'game@delete', 'POST');
$this->reg('updated', 'game@updated', 'GET');
$this->reg('serverStatus', 'game@serverStatus', 'GET');

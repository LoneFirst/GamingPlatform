<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
        <meta name="description" content="">
        <meta name="author" content="">

        <title>{{ $section }}</title>

        <!-- Bootstrap core CSS -->
        <link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

        <style>
        body {
          padding-top: 70px;
        }
        .container {
            max-width: 480px;
        }
        </style>
    </head>

    <body>
        <!-- Fixed navbar -->
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">{{ config('sitename') }}</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li {{ ($section=='纵览')?'class="active"':'' }}><a href="./home">纵览</a></li>
                        <li {{ ($section=='兑换')?'class="active"':'' }}><a href="./convert">兑换</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown">{{ $user }} <span class="caret"></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="./logout">登出</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>

        <div class="container">

            @if $section == '纵览'
            <form class="form-inline" role="form">
                <div class="form-group">
                    <select class="creater form-control">
                        <option value="ark">方舟</option>
                    </select>
                </div>
                <div class="form-group">
                    <a id="creater" class="creater btn btn-primary"{{ $verQuota?'':'disabled="disabled"' }}>
                        {{ $verQuota?'创建':'配额已达上限' }}
                    </a>
                </div>
            </form>
                @if $gameList
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>游戏</th>
                                <th>到期时间</th>
                                <th>人数配额</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach $gameList as $key => $value
                            <tr id="{{ $value['id'] }}">
                                <td>{{ $value['id'] }}</td>
                                <td>{{ $value['game'] }}</td>
                                <td>{{ $value['time'] }}</td>
                                <td>{{ $value['limit'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <h1>你还没有创建任何服务器,赶快创建一个吧</h1>
                @endif
            @elseif $section == '管理'
                <p>
                    <strong>id:</strong> {{ $game['id'] }}</br>
                    <strong>游戏: </strong> {{ $game['game'] }}</br>
                    <strong>到期时间: </strong> {{ $game['time'] }}</br>
                    <strong>人数限制: </strong> {{ $game['limit'] }}</br>
                    <strong>端口: </strong> {{ $Port }}</br>
                    <strong>脚本端口: </strong> {{ $QueryPort }}</br>
                    <strong>RCON端口: </strong> {{ $RCONPort }}</br>
                </p>
                <hr>
                <form class="form-inline" role="form">
                    <input id="convertid" type="hidden" value="{{ $game['id'] }}"></input>
                    <input id="convertkey" type="text" class="form-control"></input>
                    <a id="convertbtn" class="btn btn-default">使用兑换码</a>
                    <label id="convertlb"></label>
                </form>
                <hr>
                <a id="starter" class="btn btn-danger">开服</a>
                <a id="stoper" class="btn btn-danger">关服</a>
                <a id="upgrader" class="btn btn-warning">更新</a>
                <a id="deleter" class="btn btn-warning">删档</a>
                <hr>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#change">更改配置</button>
                <div class="modal fade" id="change" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                <h4 class="modal-title">更改配置</h4>
                            </div>
                            <form id="changer" action="./change?id={{ $game['id'] }}" method="POST" class="form-horizontal" role="form">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form id="changer" action="./change?id={{ $game['id'] }}" method="POST" class="form-horizontal" role="form">
                                                {{ $managePageHtml }}
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                                    <button id="changebtn" type="submit" class="btn btn-primary">保存更改</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#gameChange">更改游戏设置</button>
                <div class="modal fade" id="gameChange" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                <h4 class="modal-title">更改游戏配置</h4>
                            </div>
                            <form id="gameChanger" action="./gameChange?id={{ $game['id'] }}" method="POST" class="form-horizontal" role="form">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            
                                                {{ $gameChangeHtml }}
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                                    <button id="gameChangebtn" type="submit" class="btn btn-primary">保存更改</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-inline">
                      <label class="control-label">更改地图</label>
                      <select id="mapChanger" name="expMode" class="form-control">
                          <option value="TheIsland">老地图</option>
                          <option value="TheCenter">中心岛</option>
                          <option value="ScorchedEarth_P">焦土</option>
                      </select>
                      <a id="mapChangebtn" class="btn btn-default">选择</a>
                      <p class="text-danger">每次更改配置之后重新开服之前请重新选择地图</p>
                </div>
            @elseif $section == '兑换'
                @if isset($msg)
                    @if $msg['status']
                    <div class="alert alert-success">{{ $msg['msg'] }}</div>
                    @else
                    <div class="alert alert-danger">{{ $msg['msg'] }}</div>
                    @endif
                @endif
                <form action="./convert" method="GET" class="form" role="form">
                    <h2>输入兑换码</h2>
                    <input type="text" name="key" class="form-control"></input>
                </form>
            @endif
        </div>

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
        <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                var gameId = '{{ $game['id'] }}'
                var port = '{{ $Port }}'
                var qp = '{{ $QueryPort }}'
                var limit = '{{ $game['limit'] }}'

                $('#creater').click(function() {
                    $.ajax({
                        type: 'POST',
                        url: './create',
                        data: {
                            game: $('select option:selected').attr('value')
                        },
                        success: function() {
                            location.reload()
                        }
                    })
                    $('.creater').attr('disabled', 'disabled')
                    $('#creater').text('创建中...')
                })

                $('tr').click(function() {
                    location = './manage?id='+$(this).attr('id')
                })

                $('#convertbtn').click(function() {
                    $.ajax({
                        type: 'POST',
                        url: './convert',
                        data: {
                            gameId: $('#convertid').val(),
                            key: $('#convertkey').val()
                        }
                    }).done(function(msg) {
                        if (msg.status) {
                            $('#convertlb').removeClass('alert alert-danger').addClass('alert alert-success')
                        } else {
                            $('#convertlb').removeClass('alert alert-success').addClass('alert alert-danger')
                        }
                        $('#convertlb').text(msg.msg)
                    })
                })

                $('#mapChangebtn').click(function() {
                    $.ajax({
                        type: 'POST',
                        url: './changeMap',
                        data: {
                            gameId: gameId,
                            port: port,
                            qp: qp,
                            limit: limit,
                            map: $('#mapChanger option:selected').attr('value')
                        },
                        success: function() {
                            location.reload()
                        }
                    })
                    $(this).attr('disabled', 'disabled')
                    $(this).text('更改中...')
                })
                $('#starter').click(function() {
                    $.ajax({
                        type: 'POST',
                        url: './start?id='+gameId
                    })
                    location.reload()
                })
                $('#stoper').click(function() {
                    $.ajax({
                        type: 'POST',
                        url: './stop?id='+gameId,
                        success: function() {
                            location.reload()
                        }
                    })
                    location.reload()
                })
                $('#upgrader').click(function() {
                    $.ajax({
                        type: 'POST',
                        url: './upgrade?id='+gameId,
                        success: function() {
                            location.reload()
                        }
                    })
                    $('#upgrader').attr('disabled', 'disabled')
                })
                $('#deleter').click(function() {
                    $.ajax({
                        type: 'POST',
                        url: './delete?id='+gameId,
                        success: function() {
                            location.reload()
                        }
                    })
                    $('#deleter').attr('disabled', 'disabled')
                })

                setInterval(function() {
                    $.ajax({
                        type: 'GET',
                        url: './updated?id='+gameId
                    }).done(function(msg) {
                        if (msg.status) {
                            $('#upgrader').removeAttr('disabled')
                            $('#upgrader').text('更新')
                        } else {
                            $('#upgrader').attr('disabled', 'disabled')
                            $('#upgrader').text('更新中...')
                        }
                    })
                    
                    $.ajax({
                        type: 'GET',
                        url: './serverStatus?id='+gameId
                    }).done(function(msg) {
                    console.log(msg)
                        if (msg.status) {
                            $('#starter').attr('disabled', 'disabled')
                            $('#stoper').removeAttr('disabled')
                        } else {
                            //$('#stoper').attr('disabled', 'disabled')
                            $('#starter').removeAttr('disabled')
                        }
                    })
                }, 1000);
            })
        </script>
    </body>
</html>

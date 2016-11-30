<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
        <meta name="description" content="">
        <meta name="author" content="">

        <title>注册/登录</title>

        <!-- Bootstrap core CSS -->
        <link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

        <style>
        body {
          padding-top: 40px;
          padding-bottom: 40px;
          background-color: #eee;
        }

        .form-signin {
          max-width: 330px;
          padding: 15px;
          margin: 0 auto;
        }
        .form-signin .form-signin-heading,
        .form-signin .checkbox {
          margin-bottom: 10px;
        }
        .form-signin .checkbox {
          font-weight: normal;
        }
        .form-signin .form-control {
          position: relative;
          height: auto;
          -webkit-box-sizing: border-box;
             -moz-box-sizing: border-box;
                  box-sizing: border-box;
          padding: 10px;
          font-size: 16px;
        }
        .form-signin .form-control:focus {
          z-index: 2;
        }
        .form-signin input[type="email"] {
          margin-bottom: -1px;
          border-bottom-right-radius: 0;
          border-bottom-left-radius: 0;
        }
        .form-signin input[type="password"] {
          margin-bottom: 10px;
          border-top-left-radius: 0;
          border-top-right-radius: 0;
        }
        </style>
    </head>

    <body>
        <div class="container">
        @if isset($msg)
          <div class="alert alert-danger" role="alert">{{ $msg }}</div>
        @endif

          <form id="form" action="./login" method="POST" class="form-signin" role="form">
            <h2 class="form-signin-heading">登陆/注册</h2>
            <input name="email" type="email" class="form-control" placeholder="邮箱" required autofocus>
            <input name="password" type="password" class="form-control" placeholder="密码" required>
            <button id="login" class="btn btn-lg btn-primary btn-block">登陆</button>
            <button id="register" class="btn btn-lg btn-primary btn-block">注册</button>
          </form>
        </div> <!-- /container -->


        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
        <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script type="text/javascript">
        $(document).ready(function() {
            $("#login").click(function() {
                $("#form").attr("action", "./login");
            });
            $("#register").click(function() {
                $("#form").attr("action", "./register");
            });
        });
        </script>
    </body>
</html>

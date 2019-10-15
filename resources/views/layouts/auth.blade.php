<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <style>
        html {
            background: url('/bg.jpg') no-repeat center center fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
        .page-header-fixed {
            background-color:transparent;
        }
        .panel-heading {
            background-color:transparent!important;;
            border-color:transparent!important;
            color:white!important;;
        }
        .panel {
            border-radius: 5px;
            background: rgba(3,3,3,0.25);
            box-shadow: 1px 1px 50px #000;
        }
        .form-control{
            padding: 8px;
            border-radius: 6px;
            border: none;
            background: rgba(3,3,3,.1);
            -webkit-transition: all 2s ease-in-out;
            -moz-transition: all 2s ease-in-out;
            -o-transition: all 2s ease-in-out;
            transition: all 0.2s ease-in-out;
            color:white;
        }
        .control-label{
            color:white;
        }
        label{
            color:white;
        }
        a{
            color: white;
        }
        .btn{
            background: rgba(107,255,3,0.3);
            border: 0;
        }
        .btn:hover{
            background: rgba(107,255,3,0.3);
            border: 0;
            opacity: 0.7;
        }
    </style>
</head>

<body class="page-header-fixed">

    <div style="margin-top: 10%;"></div>

    <div class="container-fluid">
        @yield('content')
    </div>

    <div class="scroll-to-top"
         style="display: none;">
        <i class="fa fa-arrow-up"></i>
    </div>

    @include('partials.javascripts')

</body>
</html>

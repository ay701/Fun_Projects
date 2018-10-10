<html>
<title>
    Canoe Software Clients
</title>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <style>
        body {
            border: 2px solid #000000;
            margin: 1.2em
        }

        .clients_div {
            left: 45%;
            top: 35%;
            width: 50%;
            position: absolute;
        }

        .client_title {
            font-size: 24px;
            font-weight: bold;
            display: inline-block;
            color: #000000
        }
    </style>
</head>
<body>

<div class="clients_div">
@foreach($clients as $client)
    <p>
        <a class="client_title" href="{{url('canoe/client/'.$client->id)}}">{{ $client->name }}</a>
    </p>
@endforeach
</div>

</body>
</html>
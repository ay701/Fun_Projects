<html>
<title>
    Canoe Software Client - {{$client->name}}
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
            color: #000000;
            margin-top: 4%;
            margin-bottom: 4%
        }
    </style>
</head>
<body>

<div class="client_div" align="center">
    <div class="client_title">
        {{$client->name}}
    </div>

    @if($funds->count())
    <table class="table" style="width:80%">
        <tr>
            <th>Fund Name</th>
            <th>Type</th>
            <th>Inception Date</th>
            <th>Description</th>
        </tr>
        @foreach($funds as $fund)
        <tr>
            @if(!in_array($fund->type, $client->permissions))
                <?php
                    $fund->name = "***";
                    $fund->inception_date = "***";
                    $fund->description = "***";
                ?>
            @endif
            <td>{{$fund->name}}</td>
            <td>{{$fund->type}}</td>
            <td>{{$fund->inception_date}}</td>
            <td>{{$fund->description}}</td>
        </tr>
        @endforeach
    </table>
    @endif

</div>

</body>
</html>
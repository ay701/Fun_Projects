<html>
<title>
    Canoe Software Cashflows
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

        .cashflow_div {
            margin-top: 4%;
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

    <div class="container cashflow_div">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <select class="form-control" id="clients">
                        <option>Client Name</option>
                        @foreach($clients as $client)
                            <option value="{{$client->id}}" investment-types="{{$client->permission}}">{{$client->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <select class="form-control" id="investment_types">
                        <option>Investment Type</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <select class="form-control" id="investment_names">
                        <option>Investment Name</option>
                    </select>
                </div>
            </div>
        </div>

        <p></p>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group" align="left">
                    <label for="current_value">Current Value</label>
                    <input type="text" class="form-control" id="current_value" placeholder="$ ###,###,###">
                </div>
            </div>
            <div class="col-sm-4">
            </div>
            <div class="col-sm-4">
                <div class="form-group" align="left">
                    <label for="updated_value">Updated Value</label>
                    <input type="text" class="form-control" id="updated_value" placeholder="$ ###,###,###">
                </div>
            </div>
        </div>

        <p></p>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group" align="left">
                    <label for="date">Date</label>
                    <input type="text" class="form-control" id="calc_date">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group" align="left">
                    <label for="value">Value</label>
                    <input type="text" class="form-control" id="calc_value">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="calculate"></label><br><br>
                    <button type="button" id="calc_investment" class="btn btn-primary" style="width: 100%">Calculate</button>
                </div>
            </div>
        </div>

        <p></p>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group" align="left">
                    <label for="cancel"></label><br><br>
                    <button type="button" id="cancel-button" class="btn btn-primary" style="width: 100%">Cancel</button>
                </div>
            </div>
            <div class="col-sm-4">
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="submit"></label><br><br>
                    <button type="button" id="add_cashflow" class="btn btn-primary" style="width: 100%">Submit</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    $(function(){
        $('#clients').on('change', function() {
            var investment_types = $(this).find(':selected').attr('investment-types');
            investment_types = investment_types.split(",");
            console.log(investment_types);

            $("#investment_types").empty();
            $("#investment_types").append(
                $('<option>', {
                    text: "Investment Type"
                })
            )

            for ( var i = 0, l = investment_types.length; i < l; i++ ) {
                $("#investment_types").append(
                    $('<option>', {
                        value: investment_types[i],
                        text: investment_types[i]
                    })
                )
            }
        });


        $('#investment_types').on('change', function() {
            var investment_type = this.value;
            var cient_id = $('#clients').val();

            $.ajax({
                url: "{{url('canoe/funds-by-type/')}}" + "/" + investment_type,
                type: 'GET',
                dataType: 'json', // added data type
                success: function(res) {
                    var investment_names = res.investment_names;
                    console.log(investment_names);

                    $("#investment_names").empty();
                    $("#investment_names").append(
                        $('<option>', {
                            text: "Investment Name"
                        })
                    )

                    for ( var i = 0, l = investment_names.length; i < l; i++ ) {
                        console.log(investment_names[i]);

                        $("#investment_names").append(
                            $('<option>', {
                                value: investment_names[i].id,
                                text: investment_names[i].name
                            })
                        )
                    }
                }
            });
        });

        $('#investment_names').on('change', function() {
            var fund_id = this.value;
            var cient_id = $('#clients').val();

            $.ajax({
                url: "{{url('canoe/investments/')}}" + "/" + cient_id + "/" + fund_id,
                type: 'GET',
                dataType: 'json', // added data type
                success: function(res) {
                    var amount = res.investments.amount;
                    $("#current_value").val(amount);
                }
            });
        });

        $('#calc_investment').click(function(){
            var current_value = $('#current_value').val();
            var calc_value = $('#calc_value').val();

            $.ajax({
                type: 'POST',
                url: "{{url('canoe/calculate-investment')}}",
                data: {
                        "_token": "{{ csrf_token() }}",
                        "current_value" : current_value,
                        "calc_value" : calc_value
                      },
                dataType: "text",
                success: function(res) {
                    var updated_value = JSON.parse(res).updated_value;
                    $("#updated_value").val(updated_value);
                }
            });

        })

        $('#add_cashflow').click(function(){
            var investment_id = $('#investment_names').val();
            var calc_date = $('#calc_date').val();
            var calc_value = $('#calc_value').val();

            $.ajax({
                type: 'POST',
                url: "{{url('canoe/add-cashflow')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "investment_id" : investment_id,
                    "date" : calc_date,
                    "return" : calc_value
                },
                dataType: "text",
                success: function(res) {
                    alert(JSON.parse(res).message);
                }
            });

        })

        $('#cancel-button').click(function() {
            location.reload();
        });

    });
</script>

</body>
</html>
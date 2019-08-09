<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html,
        body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links>a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="flex-center position-ref full-height">
        @if ($message = Session::get('success'))

        <p>{!! $message !!}</p>

        <?php Session::forget('success'); ?>
        @endif

        @if ($message = Session::get('error'))

        <p>{!! $message !!}</p>

        <?php Session::forget('error'); ?>
        @endif

        <form class="w3-container w3-display-middle w3-card-4 w3-padding-16" method="POST" id="payment-form" action="{!! URL::to('paypal') !!}">
            <div class="w3-container w3-teal w3-padding-16">Paywith Paypal</div>
            {{ csrf_field() }}
            <h2 class="w3-text-blue">Payment Form</h2>
            <p>Demo PayPal form - Integrating paypal in laravel</p>
            <label class="w3-text-blue"><b>Enter Amount</b></label>
            <input class="w3-input w3-border" id="amount" type="text" name="amount"></p>
            <button class="w3-btn w3-blue">Pay with PayPal</button>
        </form>
    </div>
</body>

</html>
@extends('userlayout')

@section('content')
<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-dark">
          <div class="card-body text-dark">
            <div class="">
              <h3 class="text-yellow">{{$gate->gateway['name']}}</h3>
              <span class="mt-0 mb-5 text-white">Amount: {{$currency->symbol.number_format($gate->amount)}}</span><br>
              <span class="mt-0 mb-5 text-white">Charge: {{$currency->symbol.number_format($gate->charge)}}</span><br>
              <span class="mt-0 mb-5 text-white">Total: {{$currency->symssbol.number_format($gate->amount+$gate->charge)}}</span><br><br>
              <a target="_blank" href="https://buy.simplex.com" class="btn btn-neutral btn-sm">proceed to payment</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <hr class="bg-secondary">

<div class="alert alert-dark text-center mt-3">
  <h5 class="text-warning mb-2">Payment Information</h5>

  <p class="text-white mb-1">
    After submitting this request, you will be securely redirected to
    <strong>Simplex.com</strong> to complete your payment.
  </p>

  <p class="text-white mb-1">
    Please ensure you make the payment using
    <strong>Bitcoin (BTC)</strong> only.
  </p>

  <p class="text-info mb-1">
    <strong>BTC Wallet:</strong><br>
    <span class="text-break">
      bc1qexamplewalletaddress1234567890abcd
    </span>
  </p>

  <small class="text-muted">
   Use the BTC address provided for payment, after which you will be redirected to Simplex.com to complete the transaction.
  </small>
</div>

@stop
@extends('layouts.default')


@section('content')
<div class="row" style="margin-top: 50px;">
    <div class="col" class="row">
        <div class="card" style="min-height: 410px;">
            <div class="card-header">
                <strong>MOEDA A SER CONVERTIDA</strong>
            </div>
            <div class="card-body">

            <form class="needs-validation">
                {{ csrf_field() }}
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-5 col-form-label">Valor para converter: </label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" id="amount" placeholder="0,00" value="1.000,00">
                        <div id="amount_desc">
                            <small>*Valor mínimo para conversão é de 1.000,00</small>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-5 col-form-label">Moeda de origem: </label>
                    <div class="col-sm-7">
                        <select id="currency_from" class="form-control">
                            @php
                                foreach ($coins as $coin){
                                    $selected = $coin == 'BRL' ? 'selected' : '';
                                    echo '<option value="' . $coin . '"' . $selected . '>' . $coin . '</option>';
                                }
                            @endphp
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticEmail" class="col-sm-5 col-form-label">Moeda de compra: </label>
                    <div class="col-sm-7">
                        <select id="currency_to" class="form-control">
                            @php
                                foreach ($coins as $coin){
                                    $selected = $coin == 'USD' ? 'selected' : '';
                                    echo '<option value="' . $coin . '"' . $selected . '>' . $coin . '</option>';
                                }
                            @endphp
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col text-center">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="payment_boleto" name="payment_type" value="boleto" class="custom-control-input" checked>
                            <label class="custom-control-label" for="payment_boleto">Pagamento no Boleto</label>
                        </div>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="payment_card" name="payment_type" value="cartao" class="custom-control-input">
                            <label class="custom-control-label" for="payment_card">Pagamento no Cartão</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col text-right">
                        <button class="btn btn-primary" type="button">Converter Moeda</button>
                    </div>
                </div>
            </form>


            </div>
        </div>
    </div>

    <div class="col" class="row">
        <div class="card" style="min-height: 410px;">
            <div class="card-header">
                <strong>RESULTADO DA CONVERSÃO</strong>
            </div>
            <div class="card-body" id="result_conversion">
                <p class="card-text">Informe ao lado os dados da moeda que gostaria de converter</p>
            </div>
        </div>
    </div>
</div>
@endsection


@section('jsContent')
<script src="assets/js/plugins/maskMoney/jquery.maskMoney.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('#amount').maskMoney({
            symbol:'R$ ',
            thousands:'.',
            decimal:',',
            symbolStay: true
        });
    });

    $('body').on('click', '.btn-primary', function (){
        let token = $('input[type=hidden]').val();
        let amount = $('#amount').val();
        let currencyFrom   = $('#currency_from').val();
        let currencyTo = $('#currency_to').val();
        let paymentType = $('input[name=payment_type]:checked').val();

        // alert(token + "\n" + amount + "\n" + currencyFrom + "\n" + currencyTo + "\n" + paymentType);

        if (amount.length < 7){
            $('#amount_desc').addClass('text-danger');
            return;
        } else if (amount.length >= 10) {
            $('#amount_desc').addClass('text-danger').html('<small>Informe um valor maior que 1.000,00 e menor que 100.000,00</small>');
            return;
        }

        if (currencyFrom == currencyTo){
            $('.invalid-feedback').show().html('Escolha uma moeda de compra diferente da origem!');
            return;
        }

        $('#amount_desc').removeClass('text-danger');
        $('.invalid-feedback').hide().html('');

        $.ajax({
            'url': '/converter',
            'method': 'post',
            'data': '_token=' + token + '&amount=' + amount + '&currencyFrom=' + currencyFrom + '&currencyTo=' + currencyTo + '&paymentType=' + paymentType,
            'dataType': 'json',
            'success': function(response){
                $('#result_conversion').html('<p>Moeda de origem: ' + response.currencyFrom + '</p>');
                $('#result_conversion').append('<p>Moeda de destino: ' + response.currencyTo + '</p>');
                $('#result_conversion').append('<p>Valor para conversão: ' + response.amount + '</p>');
                $('#result_conversion').append('<p>Forma de pagamento: ' + response.paymentType + '</p>');
                $('#result_conversion').append('<p>Valor comprado em "Moeda de destino": ' + response.buyValue + '</p>');
                $('#result_conversion').append('<p>Taxa de pagamento: ' + response.paymentFee + '</p>');
                $('#result_conversion').append('<p>Taxa de conversão: ' + response.conversionFee + '</p>');
                $('#result_conversion').append('<p>Valor utilizado para conversão descontando as taxas: ' + response.amountConversion + '</p>');
            },
            'error': function(errors){
                let error = errors.responseJSON;

                $('#result_conversion').html('<p class="alert alert-danger">Não foi possível fazer a conversão!</p>');
                $('#result_conversion').append('<p>Status do erro: ' + error.status + '</p>');
                $('#result_conversion').append('<p>Código do erro: ' + error.code + '</p>');
                $('#result_conversion').append('<p>Mensagem: ' + error.message + '</p>');
            }
        })
    })
</script>
@endsection

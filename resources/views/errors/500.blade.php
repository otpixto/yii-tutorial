@extends( 'errors.template' )

@section( 'content' )

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <h1 class="big text-danger bold">
                        500
                    </h1>
                    <h2>
                        Произошла внутренняя ошибка!
                    </h2>
                    <div class="error-actions">
                        <a href="/" class="btn btn-primary btn-lg">
                            <i class="glyphicon glyphicon-home"></i>
                            На главную
                        </a>
                        <a href="mailto:support@edska.ru" class="btn btn-default btn-lg">
                            <i class="glyphicon glyphicon-envelope"></i>
                            Написать письмо
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
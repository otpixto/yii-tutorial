@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ 'Обращение #' . $ticket->id ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">

            <div class="blog-page blog-content-2">

                <div class="blog-single-content bordered blog-container">
                    <div class="blog-single-head">
                        <h1 class="blog-single-head-title">
                            Обращение #{{ $ticket->id }}
                        </h1>
                        <div class="blog-single-head-date">
                            <i class="icon-calendar font-blue"></i>
                            <a href="javascript:;">
                                {{ $ticket->created_at->format( 'd.m.Y' ) }}
                            </a>
                        </div>
                    </div>

                    <div class="panel panel-primary">
                        <!-- Default panel contents -->
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                Заявитель
                            </h3>
                        </div>
                        <div class="panel-body">

                            <div class="row">

                                <div class="col-md-7">
                                    <div class="form-group">
                                        {!! Form::label( null, 'ФИО заявителя', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->getName() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        {!! Form::label( null, 'Телефон(ы) заявителя', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->getPhones() }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="panel panel-primary">
                        <!-- Default panel contents -->
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                Обращение
                            </h3>
                        </div>
                        <div class="panel-body">

                            <div class="row">

                                <div class="col-md-7">
                                    <div class="form-group">
                                        {!! Form::label( null, 'Адрес обращения', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->address }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        {!! Form::label( null, 'Тип обращения', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->type->name }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12">

                                    <div class="note note-info">
                                        <h4 class="block">Текст обращения</h4>
                                        <p>
                                            {{ $ticket->text }}
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="panel panel-info">
                        <!-- Default panel contents -->
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                Сроки и сезонность
                            </h3>
                        </div>

                        <div class="panel-body form-horizontal">

                            <div class="row">

                                <div class="col-xs-7 text-right">
                                    {!! Form::label( null, 'Период на принятие заявки в работу, час', [ 'class' => 'control-label' ] ) !!}
                                </div>

                                <div class="col-xs-5">
                                    <span class="form-control">
                                        {{ $ticket->type->period_acceptance }}
                                    </span>
                                </div>

                            </div>

                            <div class="row margin-top-10">

                                <div class="col-xs-7 text-right">
                                    {!! Form::label( null, 'Период на исполнение, час', [ 'class' => 'control-label' ] ) !!}
                                </div>

                                <div class="col-xs-5">
                                    <span class="form-control">
                                        {{ $ticket->type->period_execution }}
                                    </span>
                                </div>

                            </div>

                            <div class="row margin-top-10">

                                <div class="col-xs-7 text-right">
                                    {!! Form::label( null, 'Сезонность устранения', [ 'class' => 'control-label' ] ) !!}
                                </div>

                                <div class="col-xs-5">
                                    <span class="form-control">
                                        {{ $ticket->type->season }}
                                    </span>
                                </div>

                            </div>

                        </div>
                    </div>

                    @if ( $ticket->management )

                    <div class="panel panel-info">
                        <!-- Default panel contents -->
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                Управляющая компания
                            </h3>
                        </div>
                        <div class="panel-body">

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label( null, 'Управляющая компания', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->management->name }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label( null, 'Телефон УК', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->management->phone }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label( null, 'Адрес УК', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->management->address }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    @endif

                    <div class="blog-comments">
                        <h3 class="sbold blog-comments-title">Comments(30)</h3>
                        <div class="c-comment-list">
                            <div class="media">
                                <div class="media-left">
                                    <a href="#">
                                        <img class="media-object" alt="" src="../assets/pages/img/avatars/team1.jpg"> </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">
                                        <a href="#">Sean</a> on
                                        <span class="c-date">23 May 2015, 10:40AM</span>
                                    </h4> Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. </div>
                            </div>
                            <div class="media">
                                <div class="media-left">
                                    <a href="#">
                                        <img class="media-object" alt="" src="../assets/pages/img/avatars/team3.jpg"> </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">
                                        <a href="#">Strong Strong</a> on
                                        <span class="c-date">21 May 2015, 11:40AM</span>
                                    </h4> Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis.
                                    <div class="media">
                                        <div class="media-left">
                                            <a href="#">
                                                <img class="media-object" alt="" src="../assets/pages/img/avatars/team4.jpg"> </a>
                                        </div>
                                        <div class="media-body">
                                            <h4 class="media-heading">
                                                <a href="#">Emma Stone</a> on
                                                <span class="c-date">30 May 2015, 9:40PM</span>
                                            </h4> Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. </div>
                                    </div>
                                </div>
                            </div>
                            <div class="media">
                                <div class="media-left">
                                    <a href="#">
                                        <img class="media-object" alt="" src="../assets/pages/img/avatars/team7.jpg"> </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">
                                        <a href="#">Nick Nilson</a> on
                                        <span class="c-date">30 May 2015, 9:40PM</span>
                                    </h4> Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. </div>
                            </div>
                        </div>
                        <h3 class="sbold blog-comments-title">Добавить комментарий</h3>
                        <form action="#">
                            <div class="form-group">
                                <textarea rows="8" name="message" placeholder="Комментарий ..." class="form-control c-square"></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn blue uppercase btn-md sbold btn-block">Добавить комментарий</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/pages/css/blog.min.css" rel="stylesheet" type="text/css" />
@endsection
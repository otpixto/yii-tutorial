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
                                {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
                            </a>
                        </div>
                    </div>
					
					<div class="panel panel-primary">
                        <!-- Default panel contents -->
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                Статус обращения
                            </h3>
                        </div>
                        <div class="panel-body">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label( null, 'Текущий статус', [ 'class' => 'control-label' ] ) !!}
                                        <span class="form-control">
                                            {{ $ticket->getStatusName() }}
                                        </span>
                                    </div>
                                </div>

                            </div>
							
							<div class="row">

                                {!! Form::open( [ 'url' => route( 'tickets.status', $ticket->id ) ] ) !!}

								<div class="form-group">
									<div class="col-md-12">
										{!! Form::label( 'status', 'Сменить статус', [ 'class' => 'control-label' ] ) !!}
									</div>
								</div>

								<div class="form-group">
									<div class="col-md-8"> 
										{!! Form::select( 'status', [ null => ' -- выберите из списка -- ' ] + $ticket->getAvailableStatuses(), null, [ 'class' => 'form-control' ] ) !!}
                                    </div>
									<div class="col-md-4">
										{!! Form::submit( 'Сменить', [ 'class' => 'btn btn-success' ] ) !!}
									</div>
                                </div>

                                {!! Form::close() !!}
			
                            </div>

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

                    @if ( $ticket->managements->count() )

                        @foreach ( $ticket->managements as $i => $management )

                            <div class="panel panel-info">
                                <!-- Default panel contents -->
                                <div class="panel-heading">
                                    <h3 class="panel-title">
                                        Исполнитель #{{ ( $i + 1 ) }}
                                    </h3>
                                </div>
                                <div class="panel-body">

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label( null, 'Наименование', [ 'class' => 'control-label' ] ) !!}
                                                <span class="form-control">
                                                    {{ $management->name }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label( null, 'Телефон', [ 'class' => 'control-label' ] ) !!}
                                                <span class="form-control">
                                                    {{ $management->phone }}
                                                </span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                {!! Form::label( null, 'Адрес', [ 'class' => 'control-label' ] ) !!}
                                                <span class="form-control">
                                                    {{ $management->address }}
                                                </span>
                                            </div>
                                        </div>

                                    </div>

                                    @if ( ! $management->has_contract )
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-danger">
                                                    Отсутствует договор
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>

                        @endforeach

                    @endif
	
					<div class="blog-comments">
						<h3 class="sbold blog-comments-title">Комментарии</h3>
							<div class="c-comment-list">
							@if ( $ticket->comments->count() )
								@include( 'parts.comments', [ 'comments' => $ticket->comments ] )
							@else
								<div class="media">
									<div class="media-body">
										<div class="alert alert-danger">Еще не добавлено ни одного комментария</div>
									</div>
								</div>
							@endif
						</div>
						<h3 class="sbold blog-comments-title">Добавить комментарий</h3>
						{!! Form::open( [ 'url' => route( 'tickets.comment', $ticket->id ) ] ) !!}
							<div class="form-group">
								{!! Form::textarea( 'text', null, [ 'class' => 'form-control c-square', 'placeholder' => 'Комментарий ...' ] ) !!}
							</div>
							<div class="form-group">
								{!! Form::submit( 'Добавить комментарий', [ 'class' => 'btn blue uppercase btn-md sbold btn-block' ] ) !!}
							</div>
						{!! Form::close() !!}
					</div>
					
				</div>

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/pages/css/blog.min.css" rel="stylesheet" type="text/css" />
@endsection
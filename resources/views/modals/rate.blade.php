<div class="form-horizontal">
    <div class="form-group">
        <div class="col-xs-4 control-label">
            Оценка
        </div>
        <div class="col-xs-4">
            <span class="form-control">
                {{ $rate }}
            </span>
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::textarea( 'text', null, [ 'class' => 'form-control', 'required' ] ) !!}
        </div>
    </div>
</div>
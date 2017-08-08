<div class="row">
    <div class="col-md-12">
        <label class="control-label">
            Выберите Эксплуатирующую Организацию:
        </label>
        <hr class="margin-top-10 margin-bottom-10" />
    </div>
</div>
@foreach ( $managements as $management )
    <div class="row">
        <div class="col-md-12">
            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline col-md-12" style="margin-bottom: 0;">
                <input type="checkbox" value="1" checked="checked" />
                <span></span>
                <div class="col-md-4">
                    {{ $management->name }}
                </div>
                <div class="col-md-3">
                    {{ $management->phone ?? '&nbsp;' }}
                </div>
                <div class="col-md-5">
                    {{ $management->address ?? '&nbsp;' }}
                </div>
                @if ( ! $management->has_contract )
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            Отсутствует договор
                        </div>
                    </div>
                @endif
            </label>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr class="margin-top-10 margin-bottom-10" />
        </div>
    </div>
@endforeach
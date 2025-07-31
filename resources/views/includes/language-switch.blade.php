
<form action="{{route('language.switch')}}"  method="POST" class="inline-block">
@csrf
    <select class="btn btn-square btn-language dropdown-toggle" data-dropdown-class="dropdown-lang" name="language" onchange="this.form.submit()">
    <option value="pt" {{app()->getLocale()=== 'pt' ? 'selected': ''}}>{{__('PT')}}</option>
    <option value="en"{{app()->getLocale()=== 'en' ? 'selected': ''}}>{{__('EN')}}</option>
    </select>
</form>
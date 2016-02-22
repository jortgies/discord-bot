@extends("templates.master")
@section("content")
    <div class="container">
        @if(count($errors) > 0)
            @foreach($errors->all() as $error)
                <div class="alert alert-danger">{!! $error !!}</div>
            @endforeach
        @endif
        @if(isset($success))
            <div class="alert alert-success">{{$success}} - Upload erfolgreich</div>
        @endif
        <div class="jumbotron">
            <form action="{{url()->to('upload')}}" method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file" />
                <button class="btn btn-primary" type="submit" name="submit" id="submit">Upload</button>
            </form>
        </div>
    </div>
@endsection

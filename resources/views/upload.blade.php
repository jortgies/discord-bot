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
            <br/>
            <form action="{{url()->to('uploadYoutube')}}" method="post">
                {{ csrf_field() }}
                <input type="url" name="link" id="link" placeholder="Link to Youtube Video" />
                <br/>
                <button class="btn btn-primary" type="submit" name="submit" id="submit">Get from Youtube</button>
            </form>
        </div>
    </div>
@endsection

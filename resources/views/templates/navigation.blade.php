<div class="container">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{url("/")}}">Discord Bot</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="{{url("/list")}}">Alle anzeigen</a></li>
                    <li><a href="" id="categories">Kategorien</a></li>
                    <li><a href="{{url("/upload")}}">Upload</a></li>
                </ul>

                @if(Auth::check())
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img src="{{ Auth::user()->avatar }}" width="25px" height="25px" />
                            {{ Auth::user()->name }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-header">Logged in as {{ Auth::user()->name }}</li>
                            <li><a href="{{ url('auth/logout') }}">Logout</a></li>
                        </ul>
                    </li>
                </ul>
                @endif
            </div>
        </div>
    </nav>
    <div class="jumbotron" id="categories" style="display: none">
    </div>
</div>
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
            </div>
        </div>
    </nav>
    <div class="jumbotron" id="categories" style="display: none">

    </div>
</div>
@extends('layouts.ext')

@section("title")
    {{ config("app.name") }}
@endsection

@section("ext")
    <!-- Client base -->
    <script type="text/javascript">
        if (window.crm) {
            window.crm.SentryInit("{{config("sentry.dsn")}}");
            // Configure context
            @auth
            window.crm.SentryConfigure({
                id : "{{ Auth::user()->id }}",
                email : "{{ Auth::user()->email }}",
                token : "{{ csrf_token() }}"
            });
            @endauth
        }

        var basePath = "{{ config('app.client_base') }}/";
        var Ext = Ext || {};

        Ext.beforeLoad = function (tags) {
            var s = location.search,  // the query string (ex "?foo=1&bar")
                profile = "classic";  // currently crmv2 just runs on classic toolkit

            Ext.manifest = basePath + profile; // this name must match a build profile name
            return function (manifest) {
                // Adjust manifest content paths
                if (manifest.content) {
                    // Adjust base paths
                    if (manifest.content.ajax) {
                        manifest.content.ajax.basepath = "{{ env('CLIENT_BASEPATH', url('/')."/") }}";
                        manifest.content.ajax.login = "{{ url('/login') }}";
                    }

                    // Loop trough manifest
                    for (var key in manifest.content.paths) {
                        manifest.content.paths[key] = basePath + manifest.content.paths[key];
                    }
                }

                // Adjust asset config
                var i=0;
                if (manifest.js) {
                    manifest.js.forEach(function (jsPath) {
                        manifest.js[i].assetConfig.path = basePath + jsPath.assetConfig.path;
                        i++;
                    });
                }

                i=0;
                if (manifest.css) {
                    manifest.css.forEach(function (cssPath) {
                        manifest.css[i].assetConfig.path = basePath + cssPath.assetConfig.path;
                        i++;
                    });
                }

                // Load order
                if (manifest.loadOrder) {
                    i = 0;
                    manifest.loadOrder.forEach(function (loadOrderPath) {
                        if (loadOrderPath.path)
                            manifest.loadOrder[i].path = basePath + loadOrderPath.path;
                        i++;
                    });
                }
            };
        };
    </script>
@endsection

@section("content")
    @include('component.loader')
@endsection

@section("extra")
    <!-- The line below must be kept intact for Sencha Cmd to build your application -->
    @if(!is_null($bootstrap))
        <script id="microloader" data-app="6d4f1fe5-4cbf-4da5-af6c-e4b4b5ca6e98" type="text/javascript">
            {!! $bootstrap !!}
        </script>
    @else
        <script id="microloader" data-app="6d4f1fe5-4cbf-4da5-af6c-e4b4b5ca6e98" type="text/javascript" src="client/bootstrap.js"></script>
    @endif
@endsection

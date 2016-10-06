<!DOCTYPE html>
<html lang="nl-NL">
<head>
    <title>NIKU CMS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script>
    window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
    ]); ?>
    </script>
    <link media="all" type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800,300">
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- NIKU CMS --}}
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('css/vendor/niku-cms/vendor.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('css/vendor/niku-cms/niku-cms.css') }}">
    {{-- END NIKU CMS --}}
    <style>
    .padding-top {
        padding-top:30px;
    }
    </style>
</head>
<body>

    {{-- NIKU CMS --}}
    <niku-cms-spinner></niku-cms-spinner>
    {{-- END NIKU CMS --}}

    <div id="niku-cms">
        <div class="container padding-top">
            <div class="row">
                <div class="col-md-12">

                    {{-- NIKU CMS --}}
                    <niku-cms-notification v-bind:notification="nikuCms.notification"></niku-cms-notification>
                    <component :is="nikuCms.view" post_type="{{ $post_type }}"></component>
                    <niku-cms-media-manager></niku-cms-media-manager>
                    {{-- END NIKU CMS --}}

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    {{-- NIKU CMS --}}
    <script src="{{ asset('js/vendor/niku-cms/vendor.js') }}"></script>
    <script src="{{ asset('js/vendor/niku-cms/niku-cms.js') }}"></script>
    {{-- END NIKU CMS --}}
</body>
</html>

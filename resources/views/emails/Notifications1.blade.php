

<!doctype html>
<html class="no-js" lang="fr">

<head>
   <meta charset="utf-8">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title>Tarafé</title>
   <meta name="description" content="">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/img/favicon.png')}}">
   <link rel="stylesheet" href="{{asset('assets/css/preloader.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/meanmenu.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/animate.min.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/owl.carousel.min.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/swiper-bundle.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/backToTop.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/magnific-popup.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/ui-range-slider.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/nice-select.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/fontAwesome5Pro.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/flaticon.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/default.css')}}">
   <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
   <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

<body>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                     <div class="row">
                            <div class="col-12">

                                @if($maildata['state']== 1)
                                    <h4 class="mb-4">Bonjour {{$maildata['Users']}},</h4>
                                    <h5 class="mb-4">{{$maildata['hello']}},</h5>
                                    <p class="mb-2">{{$maildata['message']}}</p>

                                    <a href="{{$maildata['url']}}" type="button" class="btn btn-primary">Activé mon compte</a>
                                    </br>
                                    <p class="mb-2">{{$maildata['condition']}}</p>

                                    @elseif($maildata['state'] == 2)

                                    <h5 class="mb-4">{{$maildata['hello']}},</h5>

                                    <p class="mb-2">{{$maildata['message']}}</p>

                                    <p class="mb-2">{{$maildata['Montant']}}</p>

                                    <p class="mb-2">{{$maildata['Modepaiement']}}</p>
                                    </br>
                                    <p class="mb-2">{{$maildata['condition']}} : {{$maildata['emailAdmin']}} </p>

                                    @elseif($maildata['state'] == 3)
                                    <h5 class="mb-4">{{$maildata['hello']}},</h5>
                                    <p class="mb-2">{{$maildata['message']}}</p>
                                    </br>
                                    <p class="mb-2">{{$maildata['condition']}}</p>
                                @else
                                    InActive
                                @endif

                             </br>
                             <h4> <strong class="mb-2">Cordialement, Service Communication</strong> </h4>

                            </div>
                     </div>

                     </div>
            </div>
        </div>
    </div>

    <script src="{{asset('assets/js/vendor/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/waypoints.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/meanmenu.js')}}"></script>
    <script src="{{asset('assets/js/swiper-bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('assets/js/magnific-popup.min.js')}}"></script>
    <script src="{{asset('assets/js/parallax.min.js')}}"></script>
    <script src="{{asset('assets/js/backToTop.js')}}"></script>
    <script src="{{asset('assets/js/jquery-ui-slider-range.js')}}"></script>
    <script src="{{asset('assets/js/nice-select.min.js')}}"></script>
    <script src="{{asset('assets/js/counterup.min.js')}}"></script>
    <script src="{{asset('assets/js/ajax-form.js')}}"></script>
    <script src="{{asset('assets/js/wow.min.js')}}"></script>
    <script src="{{asset('assets/js/isotope.pkgd.min.js')}}"></script>
    <script src="{{asset('assets/js/imagesloaded.pkgd.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>

</body>

</html>

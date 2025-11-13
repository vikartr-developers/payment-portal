<!-- Footer: Start -->
<footer class="landing-footer bg-body footer-text">

    <div class="footer-bottom py-3">
        <div
            class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
            <div class="mb-2 mb-md-0">
                <span class="footer-text">
                    {{-- © --}}
                    <script>
                        // document.write(new Date().getFullYear());
                    </script>
                </span>
                <a href="{{ config('variables.creatorUrl') }}" target="_blank"
                    class="fw-medium text-white footer-link">{{ config('variables.creatorName') }}</a>
                <span class="footer-text">777 Gateway !</span>
            </div>
            <div>
                <a href="{{ config('variables.githubFreeUrl') }}" class="footer-link me-3" target="_blank">
                    <img src="{{ asset('assets/img/front-pages/icons/github-' . $configData['style'] . '.png') }}"
                        alt="github icon" data-app-light-img="front-pages/icons/github-light.png"
                        data-app-dark-img="front-pages/icons/github-dark.png" />
                </a>
                <a href="{{ config('variables.facebookUrl') }}" class="footer-link me-3" target="_blank">
                    <img src="{{ asset('assets/img/front-pages/icons/facebook-' . $configData['style'] . '.png') }}"
                        alt="facebook icon" data-app-light-img="front-pages/icons/facebook-light.png"
                        data-app-dark-img="front-pages/icons/facebook-dark.png" />
                </a>
                <a href="{{ config('variables.twitterUrl') }}" class="footer-link me-3" target="_blank">
                    <img src="{{ asset('assets/img/front-pages/icons/twitter-' . $configData['style'] . '.png') }}"
                        alt="twitter icon" data-app-light-img="front-pages/icons/twitter-light.png"
                        data-app-dark-img="front-pages/icons/twitter-dark.png" />
                </a>
                <a href="{{ config('variables.instagramUrl') }}" class="footer-link" target="_blank">
                    <img src="{{ asset('assets/img/front-pages/icons/instagram-' . $configData['style'] . '.png') }}"
                        alt="google icon" data-app-light-img="front-pages/icons/instagram-light.png"
                        data-app-dark-img="front-pages/icons/instagram-dark.png" />
                </a>
            </div>
        </div>
    </div>
</footer>
<!-- Footer: End -->

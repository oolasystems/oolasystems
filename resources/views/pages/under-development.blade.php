@extends('layouts.app')

@section('title', 'Page Under Development | Oola Systems')

@section('meta_description', 'This Oola Systems page is currently under development. We are preparing practical,
    relevant technology content and resources. Please check back soon.')

@section('content')
    {{-- Page introduction: uses the same shared typography as the About page --}}
    <section class="section-block text-center" id="page-intro">
        <div class="eyebrow">Oola Systems</div>

        <h1 class="page-title">
            {{ $pageTitle ?? 'Something Valuable Is Taking Shape' }}
        </h1>

        <p class="page-subtitle">
            {{ $pageDescription ?? 'We are working on this page to bring you useful, practical and professionally curated content. Our team is building an experience that reflects the quality and engineering standards of Oola Systems.' }}
        </p>
    </section>

    {{-- Main status section --}}
    <section class="section-block" id="development-status">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="eyebrow">A little more time</div>

                <h2 class="section-title">
                    We are working behind the scenes.
                </h2>

                <p>
                    This page is not available just yet. We are developing and reviewing its
                    content so that when it is published, it offers a clear, useful and
                    meaningful experience.
                </p>

                <p>
                    We believe good technology content should go beyond theory. Where relevant,
                    our resources will include practical examples, implementation guidance,
                    hands-on exercises and industry-oriented insights.
                </p>

                <p class="mb-0">
                    Thank you for your interest and patience. We look forward to sharing
                    this with you soon.
                </p>
            </div>

            <div class="col-lg-5">
                <div class="feature-card h-100">
                    <h3 class="h5 mb-4">
                        <i class="bi bi-hourglass-split text-primary me-2"></i>
                        What you can expect
                    </h3>

                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            Clear, structured learning resources
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            Practical examples and guided exercises
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            Industry-relevant tools and practices
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            Content focused on real-world application
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Secondary information cards --}}
    <section class="section-block" id="development-approach">
        <div class="text-center mb-4">
            <div class="eyebrow">Our approach</div>
            <h2 class="section-title">Built with purpose. Designed for practical value.</h2>
            <p class="section-subtitle">
                We are taking the time to make this resource relevant, reliable and useful.
            </p>
        </div>

        <div class="row gx-4 gy-4">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <h3 class="h5">
                        <i class="bi bi-lightbulb text-primary me-2"></i>
                        Relevant Content
                    </h3>
                    <p class="mb-0">
                        Focused material aligned with current technologies, professional
                        practices and real-world requirements.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card h-100">
                    <h3 class="h5">
                        <i class="bi bi-code-square text-primary me-2"></i>
                        Practical Learning
                    </h3>
                    <p class="mb-0">
                        Examples and exercises designed to help turn concepts into
                        implementation skills.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card h-100">
                    <h3 class="h5">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        Quality First
                    </h3>
                    <p class="mb-0">
                        Carefully structured resources with an emphasis on clarity,
                        consistency and engineering quality.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Closing call to action: follows the shared About page CTA pattern --}}
    <section class="section-block">
        <div class="cta-band">
            <h2 class="cta-band__title">
                In the meantime, explore Oola Systems
            </h2>

            <p class="cta-band__subtitle">
                Discover our technology services, explore our learning resources,
                or get in touch with our team. We would be happy to hear from you.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">

                <a href="{{ route('home') }}"
                    class="btn btn-primary d-inline-flex align-items-center justify-content-center">
                    <i class="bi bi-house-door me-2"></i>
                    Back to Home
                </a>

                <a href="{{ route('contact') }}"
                    class="btn btn-outline-light d-inline-flex align-items-center justify-content-center">
                    <i class="bi bi-envelope me-2"></i>
                    Contact Us
                </a>

            </div>
        </div>
    </section>
@endsection

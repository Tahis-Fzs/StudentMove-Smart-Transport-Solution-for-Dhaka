<x-app-layout>
    <div class="sm-page">
        <header class="sm-page-head sm-reveal">
            <p class="sm-eyebrow">Promotions</p>
            <h1 class="sm-page-head__title">Special offers</h1>
            <p class="sm-page-head__lede">Time-limited student deals on StudentMove plans.</p>
        </header>

        @if($offers->count() > 0)
            <div class="sm-grid sm-grid--3" style="margin-top:1.5rem;">
                @foreach($offers as $offer)
                    <article class="sm-panel sm-reveal" style="padding:1.35rem 1.4rem;">
                        <h2 style="font-family:Syne,sans-serif; font-size:1.2rem; font-weight:750; letter-spacing:-0.03em; margin:0 0 0.5rem; color:var(--ink,#12161c);">
                            {{ $offer->title }}
                        </h2>
                        @if($offer->description)
                            <p style="color:var(--muted,#5b6572); margin:0 0 0.85rem; line-height:1.5;">{{ $offer->description }}</p>
                        @endif
                        @if($offer->discount_percentage > 0)
                            <p style="font-family:Syne,sans-serif; font-size:1.5rem; font-weight:800; color:var(--route,#0b6e6a); margin:0 0 0.5rem;">
                                {{ $offer->discount_percentage }}% off
                            </p>
                        @endif
                        <p style="font-size:0.85rem; color:var(--muted,#5b6572); margin:0;">
                            Valid until {{ $offer->valid_until->format('d M, Y') }}
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sm-panel sm-empty sm-reveal" style="margin-top:1.5rem; text-align:center; padding:2.5rem 1.5rem;">
                <p style="margin:0; color:var(--muted,#5b6572);">No active offers right now. Check back soon.</p>
            </div>
        @endif
    </div>
</x-app-layout>

@use('App\Models\MetalRate')
@use('App\Support\NepaliDate')

<x-tool-page heading="सुनचाँदी" source="भाउ संस्थाले राखेको हो, १ तोला = ११.६६४ ग्राम। प्रति १० ग्रामको भाउ हिसाब गरिएको हो। कारोबार गर्नुअघि पसलमा दर बुझ्नुहोस्।">
    @php
        $latest = $rates->first();
        $previous = $rates->get(1);
    @endphp

    @if ($latest)
        <p class="tool-lead">
            मिति: {{ NepaliDate::format($latest->rate_date) }}
            @unless ($latest->rate_date->isToday())
                (आजको भाउ राखिएको छैन, यो पछिल्लो राखिएको भाउ हो)
            @endunless
        </p>

        <div class="tool-grid">
            @foreach (MetalRate::METALS as $metal => $name)
                @php
                    $change = $previous ? $latest->{$metal} - $previous->{$metal} : 0;
                @endphp
                <article class="tool-card tool-card--plain">
                    <strong>{{ $name }}</strong>
                    <span class="tool-card__figure">{{ MetalRate::rupees($latest->{$metal}) }}</span>
                    <span>प्रति तोला</span>
                    @if ($change !== 0)
                        <span @class(['tool-change', 'tool-change--up' => $change > 0, 'tool-change--down' => $change < 0])>
                            {{ $change > 0 ? '▲' : '▼' }} {{ MetalRate::rupees(abs($change)) }}
                        </span>
                    @endif
                    <dl class="tool-facts">
                        <div>
                            <dt>प्रति १० ग्राम</dt>
                            <dd>{{ MetalRate::rupees(MetalRate::per10Grams($latest->{$metal})) }}</dd>
                        </div>
                    </dl>
                </article>
            @endforeach
        </div>

        @if ($rates->count() > 1)
            <h2 class="tool-subhead">पछिल्ला दिनको भाउ</h2>
            <div class="tool-table tool-table--figures">
                <table>
                    <thead>
                        <tr>
                            <th>मिति</th>
                            @foreach (MetalRate::METALS as $name)
                                <th>{{ $name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rates as $rate)
                            <tr>
                                <td>{{ NepaliDate::format($rate->rate_date) }}</td>
                                @foreach (array_keys(MetalRate::METALS) as $metal)
                                    <td>{{ MetalRate::rupees($rate->{$metal}) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <p class="tool-note">सुनचाँदीको भाउ अहिलेसम्म राखिएको छैन।</p>
    @endif
</x-tool-page>

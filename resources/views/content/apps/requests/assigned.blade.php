@extends('layouts/layoutMaster')

@section('title', 'Assigned Deposit Requests')

<meta name="csrf-token" content="{{ csrf_token() }}">


@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <!-- DataTables Buttons (client-side export) dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
@endsection


@section('page-script')
    <script>
        $(function() {
            var dt_assigned_requests_table = $('.datatables-assigned-requests');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var dt_assigned_requests;
            var autoReloadTimer = null;
            var assignedLastMaxId = 0;

            function playNotificationSound() {
                try {
                    const audioEl = document.getElementById('notif-sound');
                    if (audioEl) {
                        audioEl.currentTime = 0;
                        audioEl.volume = 0.25;
                        const p = audioEl.play();
                        if (p && p.catch) {
                            p.catch(function() {
                                try {
                                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                                    const ctx = new AudioContext();
                                    const o = ctx.createOscillator();
                                    const g = ctx.createGain();
                                    o.type = 'sine';
                                    o.frequency.value = 880;
                                    g.gain.value = 0.05;
                                    o.connect(g);
                                    g.connect(ctx.destination);
                                    o.start(0);
                                    setTimeout(function() {
                                        o.stop();
                                        ctx.close();
                                    }, 180);
                                } catch (e) {
                                    console.warn('Audio notification unavailable', e);
                                }
                            });
                        }
                        return;
                    }

                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    const ctx = new AudioContext();
                    const o = ctx.createOscillator();
                    const g = ctx.createGain();
                    o.type = 'sine';
                    o.frequency.value = 880;
                    g.gain.value = 0.05;
                    o.connect(g);
                    g.connect(ctx.destination);
                    o.start(0);
                    setTimeout(function() {
                        o.stop();
                        ctx.close();
                    }, 180);
                } catch (e) {
                    console.warn('Audio notification unavailable', e);
                }
            }

            // Cache status indicator update
            function updateCacheStatus(status, loadTime) {
                const indicator = $('.cache-status-indicator');
                const statusText = $('.cache-text', indicator);
                const statusDot = $('.cache-dot', indicator);

                if (indicator.length) {
                    statusDot.removeClass('cache-success cache-warning cache-pending cache-loading');
                    if (status === 'CACHE') {
                        statusDot.addClass('cache-success');
                        statusText.text('CACHE');
                        indicator.attr('title', 'Data loaded from cache (fast)');
                    } else if (status === 'DATABASE') {
                        statusDot.addClass('cache-warning');
                        statusText.text('DATABASE');
                        indicator.attr('title', 'Data loaded from database (slower)');
                    } else {
                        statusDot.addClass('cache-pending');
                        statusText.text('PENDING');
                        indicator.attr('title', 'Loading data...');
                    }
                }

                if ($('.load-time').length && loadTime) {
                    $('.load-time').text(loadTime + ' ms');
                }
            }

            if (dt_assigned_requests_table.length) {
                dt_assigned_requests = dt_assigned_requests_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('requests.assigned.data') }}',
                        type: 'GET',
                        data: function(d) {
                            d.mode = $('#mode_filter').val() || 'all';
                            d.status = $('#status_filter').val() || 'all';
                            d.start_date = $('#start_date').val() || '';
                            d.end_date = $('#end_date').val() || '';
                            d.search_term = $('#search_term').val() || '';

                            // Handle request type filter
                            var requestType = $('#request_type_filter').val();
                            if (requestType === 'pending') {
                                d.status = 'pending'; // Override status for pending requests
                            } else if (requestType === 'rejected') {
                                d.status = 'rejected'; // Override status for rejected requests
                            } else if (requestType === 'progress') {
                                d.status = 'progress'; // Override status for rejected requests
                            }
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        dataSrc: function(json) {
                            updateCacheStatus(json.cache_status, json.load_time);
                            try {
                                const arr = Array.isArray(json.data) ? json.data : [];
                                if (arr.length) {
                                    const maxId = Math.max.apply(null, arr.map(function(r) {
                                        return r.id || 0;
                                    }));
                                    if (assignedLastMaxId > 0 && maxId > assignedLastMaxId) {
                                        playNotificationSound();
                                    }
                                    assignedLastMaxId = Math.max(assignedLastMaxId, maxId);
                                }
                            } catch (e) {
                                console.warn('Error checking new assigned requests', e);
                            }
                            return json.data;
                        }
                    },
                    columns: [{
                            data: 'id',
                            name: 'id',
                            visible: false
                        },
                        {
                            data: 'trans_id',
                            name: 'trans_id'
                        },
                        {
                            data: 'approver_name',
                            name: 'approver_name',
                            defaultContent: '-'
                        },
                        {
                            data: 'mode',
                            name: 'mode',
                            render: function(data) {
                                var icon = '';
                                switch ((data || '').toLowerCase()) {
                                    case 'bank':
                                        icon = '<i class="ti ti-building-bank me-1"></i>';
                                        break;
                                    case 'upi':
                                        icon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="39" height="26" viewBox="0 0 39 26" fill="none"><rect x="1.02119" y="1.31843" width="36.8377" height="24.0988" rx="5.16402" fill="url(#pattern0_490_1366)"/><rect x="1.02119" y="1.31843" width="36.8377" height="24.0988" rx="5.16402" stroke="#ECEFF2" stroke-width="1.14756"/><defs><pattern id="pattern0_490_1366" patternContentUnits="objectBoundingBox" width="1" height="1"><use xlink:href="#image0_490_1366" transform="matrix(0.00413347 0 0 0.00700174 -0.0581692 -0.396222)"/></pattern><image id="image0_490_1366" width="256" height="256" preserveAspectRatio="none" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQAAAAEACAYAAABccqhmAAAgAElEQVR4Ae2dCXwcR5XwZceXnIQkHBsIOXzIli3bimPZ1kxX1ehyQoDAAot3OTZsFljH1kx31Yw0I8mypLHsJIQchC/LHRZYWGBhYb/lWu5wLCzwhTuQ+3B8yJLtOA4EsI6pT6+63kxr1CMpB1iSn36/Vvd0V1dX/eu9V6+Ori4roz8iQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIALPDYG59fX186aylZWVzXluHvmXiWXr1q1nTCVfEGaSFM2ZajyzKRzwm4RL8WWQj6luxfeG/Z6qvE01XNgz6NzTJHC6wT7d8hsmDsQgjMoMP2cKVSn1eqXUu6R0r5PSuyGZlHaDY+8GlUpcr1TiepVK3OS6115q8zwjBMLzvLenUqlbksnkdclk8gaztcgbkmbzbkilvOuTLZBH9x3xePySkLzNhXNKqSgwGhMPxof7fLyF+CHu0huGs3uMZ7J9/jkTxQ3Xnln8Sqnr7dYDsrFt27b5IVzsqfzOyIOU8lql1H8mk8mPJ5PJfwvbUqnUJ5LJ5Gc9z3Ps3YZxPib/wMR3MLX8oru3li0oujb259aqBeWdjS8de5J+TZXAHKXUg8lkUqdSSre0JnVrOmW2llapcUu1eBo2pZrX24jDCm2qz/xzhzPCs23btnOUUn9IpVIat5aWlnz+WtNKmy0DeUvo5ubmF9uEBY2byWcyKT9u7m1t1a2lNsstk2nVsOXjx+eM2/uckTfGm8lkNGz4e9zePicff8bTrbDZ+DOZlPY3Px1tbWntb226rW3iDZ8LeQVmIBdKqbtc170whE1xOc5VSj3ky5LPHOIptbnutVUTxGnK4Eh66Sf6vEsaIFy2rCxU5s5V9eeeueuKr5Wp+nOLE0S/SxMwgD1v+1qlvJPJpBxKJuVJlUwM4pZMuYN2+1My5Wql5I9KRzd9rmC71fO8K5VSIMQnlVKDyWTSbnIwmZQmb6kW949g2JIt7mdtDoLKb065rrswmVQPWIX4UyqVGgzdWtRgqgWe48efavEGJ9788HCP2Wy8mM7QZ0AYDF8i/ny52XQo5Q36mxoEDs9g01LKL5fiU2YV03XdS5VSQ1LKYSmBQXIIt1QqNWS3k5bjDyeTmEeuWbLocHrV7wdaVn4OwoYYAGMQyjsbN5/d1aQXdzfFTZxby55uv8VkSZl917HTS0o3oRTU7N6QVPGcSiZ0cINzUsXBKECYW4AEKth0pYLpk1K+AwyAFfgc1Ez+JnUyKSGfuWTKHTLGLRVXkB/kYvNmBMx13c0Q3taGOaVUIC6ME/Z+vPl9ytUQd8mtOHw+fcE4w47tcyaKG649w/ghf7BJKc1mGZ5MJBJLLZcxRhKZeZ4nbVgwAhAHsM9vNj4wQHDuRogLy8rGa3ao6H2tFfVHMmv18ZZ1uSMt62rgIl4zAevLTMftgp769sU9Dbq8u+HesmzNYnON/k1KwBSiUu5n0QAEFR+OrfKDURiB38mk+yqINazQJn3aKQiglPqxFbbhgvL7iqqUB/kCAwAKOhLWtEHBVsrLWAMwVFr5Z48BQFZBAwC1ejwe32CLsdgVN7Ikpfy85Q2c8ooPx4G4RuC356VeUUqWPmNr8COty7InUhX6iZZq/bha9+8QfowBsImZszf29Xl76vSibJ1emGUJc5q8AEtngp3rus+T0u2zBmCk2AOwv43ySxU/Go/HX4DMJ4j2VF8ywui67nIp5R+tIBbV2hK8GVD+EVP7JxO/RWUPS3wy6X0ZDQAqR/h+dngAmLeA0oIyPymlPL9U+SulzlVK9VveI3CvPTaGwP42yi+lHNixY8d5peLCMnii5aLvPtlysX6iZeXw8da1uk+u3ATXslnTF+B7ITtrz5+zVzwxb4/Qi3YLXd7DHyIvAAmW3hsLLmX8clB+rOmldHPWGICC4DbkH7tfstH54EvHfUqvoHeilHqryZt0h8PyBeekdKFtrD0Vfy8kGu8N5nPbtm0vVMo7ZnmMhMVlryGvv/wem22FMnvO0iClO+xz9L4bVrDITMrEy/xwhiuwLSlLUrr/FWRcFK+Rr2OpCy460fKS359Iv1gfS18weDSzRB+SFZ+BsMYA2Bp+Qbf4m4W76/TCnthIeU/d0OJsnS7P1nkmTvICitDan4VCc68zhabig6a2LxIgW4jGAHie1wq3T1RThj/tL37WGjf5MauY1oDlDZpRDpu3EQjjJeN/C6lELsFj13VfZeMxyl9CsJ8zhbPPenrx/ZkMQLD8lfKyQS5YqsgMho8h7WBUwxgF45IykYT7w2QJ3f/Hkxe86cnWC/Tx9PnD/e3n5/raX5rra1up7+vaZLyAbdtqzNBkeU/sdl/pY4PlPbERc9xd/3BZa/WZNo3TusJCjqdkL6X7A1NoKj5sO/nyggcFhgUJe8/zTCcM9viekgRP8aFbt24tV0o9GlTcoGIF86aU92QikbjARp0XloJguzcHBRvvnVZ7v6PWlNfTSZfNV76cwxhBfP75RL1lVNz+N6el9H5k4zMeQ1hc9tyI67qXmZt8V95G6++wjd/fevEHT7Qs00fTy4b2dS7RD+9aNtTfWaUPdG34D3vDnLKtZWcs7qn/tekA7GkY8ff1Q7A/c1e9NOHICxjDF34YId++ffuS0c6tP5hCswJUXGhKeaaGVMq9b+vWrRNPxhj3mFNywghnIpFgfvvT1EilBNwKqvutiVIqpXeX5QKeBPCYfpvfSTvldBWU2ucTLHc8tmFs+XsHmpubzwrhZGRpx44dy6R0/wj3ogHCePAcypKU7j2BiUUhUZaVwcSf/tal9x1vWamPtlaO7OtYkXu0c2Wuv70qN7CzWj/asS4KN17mrrm0vLth2B8BqM+Vd9fnyq0hOLO7/pGy5npMc96whz7wdDqJNZvnxf/BFs5wvvOvqAkAQ4M2zActo1DrP134oUs5OhbdCR1ZZmgzX4MVmgBWuG3TJtEF6UcuNi9GYFzXrQoK8mw7RkOA+5D82fL3PhXkguWNzDwv8Y9WTkL7W+w1098ipfc+e/84WcLa/1DL8pojqZW546lV+lhqde5Iek1uILNWP55ZNzTQtlo/tmuV6UNYn1wrsdY3yt9dn8PfZ3U36Od11puhXfAUMM20t5M2PC/xL7bAh/LDfwEDYC25qSU9L/EmAIcFPt0hJpPJb4IBwA6soGDbfOW9As9r5jY/QYE0BmDbtm0vUSrxGugHkDLx6tmwQX48L94EHXETKa01CmgktwMjNLCB8jfMlPI+mpelgAwh97GyFH8D3B8mS3facf2+9IrWIy0r9NHWlUNHWypzx1qr9bGWS3NHWi/NHW5bq/d3Veqv3rL50vVq9UdR4UO9gK6GR8sy7GybXvICsOCuueaaRVImHrIFVBj+s4WHSmKv/765ufmiGQDRFLBS6iVKqRPWAOQVPSiMBXfU27dt27bTcuKIlN53gclERtJeH0wkEqtKlb8vS+7Dlu+4UZKgLEnp/m779u04Zz9MIc25w5nlXxrILNEHOy4aOtCxTB9uq9aH05fl+jIb9cG29cMHO9fob93Mv3mZV/VoUPEX9zTp8u7G3KJsg17YUz9k9tmGlEk7eQEGg7HYrrsjIiVM7bVtNpwBGLDeKBggKLbwp/UOaxQp5VY7jj1OGK2QmqaBn3fv4zZTwdp/TD4h3tmyYQ0ej8dXwvTvfPmHN5Nw+O/n2G80Boz1JJWKR5FrUNnxnH2Gjcu9syiO4E+j/PfEV72gP1Nx1BqAkQMdl+T62lfrvrb1ui9zWe5g+3p9uKMq94N3MF2/Y4U+v93JndMl9KIuAX0AekFXQ25BV1Nufs+WkYXZJr2wp2kfeQEWMwqA53ntUprJMENQaMHCsgUG58xsLinlbrgdFSxYYtPpGNOnlPpnv/2vSg5HFYxb4m0zIW/PFWdkNFmb3SqybbO77wpjhLI0akg6rPxMIkue9rxET1hccA6H/w63rnoVtP2PtywfGUgvzfVnYFup+zOr9EBmVc5sbZX6572bcjffeHlu7VuX6fM7NuYWdUdgLoBe0LNFz9/1yty87qv0nJ6mobm7G3XZ7voWw5C8AIOhTEr5VTQAYcpfsORmOueEwz9+jNPjPwj46Ourd5fyAAr5Mp7Pn5qbmytsysPc0emRqec2FcbTkdItOUciwMiMAHie99eQBDQexclRyv2arTTsqMrYztZAfNp13Tp7/ziPCw3AkdaqW06kVukTyZWDR1uX5wYyFRq3I20r9JFMRQ729++6LPeF6xtz/+Ct00taL83N27VZz+vh+ozuJj131ytyczuvyp3Rc8XIvGyjnrO74bEyt/Z59tmnS1kXF1V++O+vpJTHwQAECwcNAZzDNrJSqtTwz7jIT/EJK9iyWill5v37L7OEejfo2v7kFKf5lDwe5khI6T1iy3tcMykoE1K6j7uu+yKb0KDimGMp336+Uu4T1gBM0t/i7r/66qtxck6pvM8ZaF39U5j/fyK1dPhouqD8YASMAQAj0LZC79+5Vt/dy/Xtu5he5a3TczoivgHoiek53S/Tc7uuyC3s3qIXdTcMLexu1OVdjWYi22k7IoAW3HXd1/nKLyea2WaHf2To8E+p0jtV59EdlVJ6tvY3L+2gUcO9FW7j2irllXwb7VTl48/8XGMkYRGOIh7jmoCB4d+vhKUJZUkp93U2rinIkvtJG9e42h/7GA7Kysr+9KqTx1or9LGWpblQD8AaABgO3Jdeq79/4xX6ytYafXaqRi/qdvScLAfXX8/taswt6mrIlXc1jizuAgPQsL+sreYcm4agMQvL4uw7h4UmZfzd0AHoeYnB4hc2YPKMPWfa/57nlRr+mW6ATIEmk8nPowHw+wHGu6MF7ybxcsgEcpluGXqu04NGclS5O63STtpmH53c0wbpwHsxTchMqcT/gbgmmv4L/S1eEt638LaFxQXncPivv3XFtVDr96dXDPdlVuT6A+5/sQdwLLNCn0it1L/uieju3jq9rmW9Pqtjo28AsjE9rzumwQBY5R+C/ZldTRmTh9O4L2Cu5yV+BQZASjf0jS00CrCgw0TDPygM02UPb6Mlk8l+bP/777SPNQCo/FK6U3obbbrk7blMh1LuN6zSlmyzWwMBbfbN9tlhtfbcUQ/gVzbshE0JL+md3CF3VNq4xtW+OAHoSHr5J+Gln4PtK4cOtq3S0PmH7f9iA3A8vUI/marQD+2s1p/sdfSbOy7TL0pX67Ieps/IcmMA5nc35GBb1O17AWd2NR4oy+ZXDRqXjueS83SLy2Q2Ho+vkdIFyw8eQHCxh/y72/Det/UEfoau2XTLTDA9WBtJKV/m1/r+YhbFBsAKqmnaKOX+XxtHmBDAORD4WbMhI39i05Tb7A/CSkhB1kFmIEuBpsK49r8xMvYdE1e5P51Mlu5urjprIFOxH4b/+tqXjvRllhsPIOgFBPsA4HigrVI/srNK/6J3g761q1pXdmzQZd2OXtDN9YLumC7rbdRlvQ25BT2mCTB0VneTXtDdaLya06ovAF04z4vHbe0/BAYg+L42uv9Q88Ox58241X9gwU8wZJB+s2IP1mRGGP3OTTQAYav/hMj67DrlefHXWiaTttk9z/2wzf2Y2r8gS17cxhXelDDvl+wYVMlm7Sq35EpSWPv3JSvqBjLL9ED6EjP019cGw38VupQBAOU/2LFaP9ZRpR/rWKW/3LVev6arVp+X2ajP3Mn03F08bwDQCwADUN7TdPB09AJMTTdm9Z/ApB9UkKJFG14NAoC1x3RXBSnlj6wRMx6Mb9z8JkCwZ9tvBpRe2BSWvYKpsq7rCinjsVIbDGnBNGLoVIOXj6bz5udhR7WU7qcnVFr/DVCctPOWEuU/qSwpz80pCcvLbR9Rye3anWAlKRz+O9i6LAu1/0DmkiEY++9rX2aUfyID0JdZq/vT1XqgtUr/qnuzvu36Bh1JrNHndwhdtjOm5+9u0vN7G/TCbOEdAZg5CEuIGXk+nfoCxq3+UzQByB/+U2bFFqXUMVgIwyp9mJs8XeyBSZv/Npq/+g8YMezHsMJuhjsL7X+v1Oo/KNhfsPfhm3D5V6Qxvpm298u2YAwh/cFz+Dtw7g9KbV9SqvxBlpTyDlkO49r/YACSKjGiUtu0l7x2SrJ0KL3kO8YApC8Z7m9bpiczAEcylfpoy1qzHWup0g/tXK+/cUOdfltitb4wuV6fAbMDsw1mAwMAG74puKin4WBZO590RaLpIuTPNh3GhXNdd0uwoAOFHRBwhWu5zajVfzzP+0es/QvKD5OYCkKfX/3Hc0uu/gNLnuHqP+F8Cp2KGPdM2gfzFDzGPBRmSLo/KCF0U5elZGLIr/2vnUiWjNE94L70wr7M0t9ZA2CbAL77X8oDgFEAeF3YvDKcrjDNgPt7o/p9HRv12pbVel5njVkjMGgEgu8ILOhp2GnyONu9AHThPS+xFwp6oiEbWNLZtv9n4Oo/ZtYiGjDbqVkwAOgBeJ5XcvUfKeNXWWXIt5FBUWbTZmVg3Ni/zSNO/70OlANlB40B/p6SLCUTQ16qWbvJHWm4H/sOMC7Yo/t/qHXZm/oyS6H9P2z6AMADaJuKAajQMGcAPIYDHSv1vckK/f1bG/XrejboM9UKfXYPG+cFLOxpGPGNQv2hsmz0+TY909nLDSJ75sf51X9gjbeiJaRQwG0tmkskEhvtk8Z0AD3zp//57vRntslH/Da/Khra9F1dyJ9V7Ke1+o+9J+9JzNbfWP7ICdaKnKj8lXL/xxqSkkOJMPbfnHJz1yZLyxJ2AB5KL/8AKPFAeskgeAGmCTCJAfBHBJbrgfYVuq+jUu/fWakf6Viuv79nvd5zU6OuSa3TL+qM5g2Aqf2z9WPeFFzU3dBh8jmLvQDbrg2s/gM1WtHbf7bgccXW+2fS6j/+zDZ/Ceqx7f8xTQArqFNb/Qdd4dmq8MX5KpQ/GEzvMHxRKcRkG1mCz6dJ6T5lDcC44T+MCwzA9hY5qSzdta1sfn9m+X3GAGQuGZmqB2CGAdtX6MPtlbqvfa0ZEXg4fbH+yd5q/YmPv0E3ddTqF3eUMgDgBTTpxV1bnijb2VRypeMQBjPrFLpsUrpvQYttCqjIA7DrAeIQ2YdsLqd17Y8u5WiTZqcV6PDhqMDwn+f5q//gvTafKNiTviJbrDiz4bdVWPBysPzN+vvFaz+iLHmed/UYWSoaTcKycJNKb0+lSspSvvZvWV7Tn16e8z2AS3JTNgCZSn0svVYfa12vj7Ru1APpDXp/Zon+3+ur9U0fvErXdkf0CzsLTQBYMQi9AH+tAHhVeMsXy5LR8pml1U8vtUaJpfQKq/9AgRUZALssuKklXdd9MzwCC/zpPe4vH3oqM9tQyMNW/0FjIKX7T1MQ7FnTHAAmyMUe2+E/3/0PKX+UpTtQye1+DBOMy0sqfW0q9felZCk//bdlZYsZ729bNuQrP0wEwj4AeA0YNr8/AHr+j2Uq9ZHMag1fDDqSrtZHWjfoIy0Rvb8tou++fpP+/O11ukkt1xe2bdCwTsDC3YFhwO76HC4Usijb9OmAtM7ePgDXfflCpbzC6j+F9rAxBFb5/U+CKW9Grf4DH/M0b6ON/YpR3sBZATfDeVKWXP3HCLZS7icmEuygsBd/cgs+qhrciq8H7z3Vx1ZBUflzyaQEPvA9Q1DkD1ilCPX+YGagUu4DNg/jhv8wbrgOzYS2traLbXxhCmbODaRXfDFvAEz7Hw2AvwZAX2YVTA3WsCT4sfRqfRyWB2vdoAfSG/Whjsv0A8lKvU+u07/Z06Df885G/ZpdG/WyTI0+p5Pr8mwst2C3X/P7awb6C4gu7m64I6D8oXkNXJ+xhyZj8Xi8FoUuWEDmnPUEZH5ZcPd7MyG3WDu5rvt6mw+zrFm+b2Osh2NcWylLr/4Dr6kq5T5mOU0m2LDwJfSWDzXHtw8Wb/HEjkHYEm6z2eClK//FK7NePixScso3SDukw34Q1v9OopLw0Q1UVNyjOBhZkrJ5U0lZKgy5Wk/C+z7eHLI38R+Iv/QF/emVR30DsNS2/30DAEoP7wP0Zao0GAFYEAS+DoQG4HBmg743Vanv271Z//y6Jv3+Tq7/utPRF7XV6uft4np+VhjlBw9gQbZhBHr/7fJhtwXSM2uVPz/s4nmJdlto49vI+ZoTpmwmtEzGZ9TqPzKZuN2k23zYZEepD5uaUQ8pm8NW/zECALP+gBEaSNijoBftx0wOyn/WO/+5bvgs99hPcre3ZzRsHR1t026znsoRpfyPvljFKFb+oCxlSspSgZ8ZSnST7h6ID411QOnyw3+HUyuuGkhDTb98BGb/BZsABzpgbN83AkdbV2lYJeiJ1tV+E6DN7/m/r7VS//jGJn3jra/VsXRMX5QR+qxd9Xp+luXm9LIcfDEIlB+8AN8Q1O8NpGNcPgPXZs+hlImvYqEVCXPhk2AKlKdZe16z+Q57cQfQdKQBgqVU/G6/P6N5RJo8FL5sHGzaSJUIXf0HhXNU4buRUZjy23NW+d3vSendCstbe5773rDNdXe8N7jF3R3vnXZbvPlmpdxrAgt+QDFPqBRSul9BTuNkKTBfAq4lVKLRys24WhbH//vTFTcbA5CuGDQGINAECBqAYy2rNKwShAbgcPsK/UD3Wv2TG5m+Ncs1y8T0izqv0Gd1XqHndzflynpFbs4erhdkY2bMH6YDz+u103/9RE2Yz+ko7083TSaD27dvN6v/4Ph+YZacnSDjDweaj3+qZHxGrf6TSCTWYdMlqOz5Dk7fu/FrfxWfcPUfKd1vg9CGDf+B8uNmBLswR+Lplsm0DY+GsEQCjSxN9o3EsUbSO/TWt74Vl+MuEW3ZnIH0qp/6BmC5mf7rzwEoNAEOtK/SB9uqjPsPXgAYgkNqpe7buUHffdvLdEfnBs1aq/ULkpv1vJaYPrOzwSwCMse8Bdg47E/4adDze+v9Lwb7Bm7WK3/e7fI877X+2n/+BJliA2CVxXwXQKoE9opOa0DYa++6rofpH9f2zzdt4qZpo1QibPUfk0/4LBh8HswaAOwcy/dsBwUbltLCZcRBaWbqBh4epn2yWh+NA6wNCIxgRmXQINpzyKt4KDFMlsy5A6llK/vTK09CLz+8+gtv/5mpwOZ14GWm4+9A21ptNmgKtK/Uj3jL9KPdUf2j65r0TbuYjrVt0C9oq9EL25memxG58l0NZmXgObvrh+furvcnAXXVX2MsUMhnyEpZphl/HgtNSvluu/xX0eo/YzwAYwC8ZGJGrf4jZeJzaACCtT4cBzwC37tRO8at/lNglPi7oGAXCfQYwYbFNK1wjHNrZ7zQlMhAgZN3K7CxnZjj+kisobQdrolmiA6NdTBqHP470rJkm7/6z/JhYwDal+mgAYDaf1/7ev1Ix3r9SGeF2e7LVOnv9MZ05rrL9frkBn1Rh2M6/BbtiuYW7mK58u5GvairfnhhNqJhK++u22qePYtn+gXZFh/PHfUAfmkNwHCh9vdnyBkrbmcEShUfTCS2lfz4Q3HEp/o3zFTzPPewVfzCh00KoxrQIWiUX6pE6Oo/KNjQhp9MsAtNA/etkHe891Rz+As+f46U7s8sJzur0q9E7Lmg5zQEn1SzaRvnAeAEoGOpCz95NG1e+R0y4/7tyzSsBuSfg86/Kv1Y2wZjAB7sqtC/7F2l77zB0T2ZDXqtt1aXt9To5+1u0ouzDbnFu0VuURY+Ed44BN8GWNQTGVqYrX2FScNpqPwGuv2unbXIhUkfUGC4oWBLFS/18Ye/oIxN/ihUPNd1rwgKXr4JEMhbYGZbydV/oIaS0vut5THZ8N8fXdddXkqwJ0/9jAxhZAmWhrNDn/mREpShQDng8N8vJ+tEPnx12ZmPt7xkPyh8f9vSkb42+PjHxcYA+AuCVumjLdX6aPsGva9tjf5FZon+xo2Xaa9nvd7Yuta87nsejPPvbsjN723KLept1OXZhiGY3lve0/TUoi7hLz9eXz9vRlJ/NolGt8vzJlmxxe/csm9/JW6FZ6KCPZvn/znvxfSN+xb92HF/I6QBAxC2+g8O/12KbwmWateikVTK/fGfM2/TMe6CLCW2W4UfP5RcNPwnpftuyAuWVTBfWPv3JV9YdzT9Em0W/rDtf5gKDE2CY63wReAq3Z9eqw+k1+hf7Vynv/euep1MVehL26r0Oe3V+oU7I/o8+BIQGIA9W2Dhj6FFvVv0gmzj4/O76jaZZ56Oym9hG6stpfwMFlrQWgcsNrTj7Cy5xAxb/cf9X5sP3x0NGIAiRaz22B0AAB2eSURBVIZv0V9queTb7SjYo7MIFTKybdi8dxTghK/IvgPiCRNsG/9s3FlZ8j6FnOw+zynA28gSLDtWihMO/x1svaCnL3OR3tdZMbSvfXWuP11llH6gZXUOJvwcbFuRg0+C/zyzRN95W13ubZ1Obk1rrT6vbb1e3LNZn9MdzZ3dLXKLdzfq+b1Ng2AE5u3ZcqgsW7/WFEL2NKz5g9Lnr9iiSq7YYoUdJ7VMacWWYPyn6NgIY+i36IsMQMGwTbz6j5Tuf04m2GgYPM+7EvJ9mhmAsubm5rOU8g5YTpM0k7zjMPRs5cOUV5is7G+/5DsH2pbqR3ZWDu/r8Jf1wq//wjJfj3VUjvwquyb3lZtq9I7W5bnKTK1etLNBl3fHNLT3z9rNcmfujkETYBBq/vl7tjxctqfRb56d5spvajml1BZYIBOtMwpxkfXG/oH/toUE98IGBTfttm3bts2HdD6d79pBBx/cE6a0Sl1zrlJu/0SCXTAkp+Uy4laWEvXAaIqy9NUwhbfnjEF4IFNx4f72pb870LZcH8isyR3IXJrra11nPv0Nb/YdVOuGD+9y9Ld6mX57d/XI6q61+uxdm+AlHn12V1PunK6G3Jm763X57rrBxbu36IXZy39Tlr38AvOM01z584KulNprDUDJD2RiG3n080/eBIU27S5J6X7EKq0dcy50aqKgouJOtPoP1OgYHoXbxpt3b5HRJMuITztGz0WC0Ggq5WWRd1hFYs+ZsoBp5/BsbGIF04Hu/0PtK964vx2Uv2IYFL8ftnRlbiC9Sh9Wq4cOdwv9w97GR3e3bf5u9c5qvSBbMzJnT21u/l6RO7tni34eLPHdywYX7QFvoOmu/Ko+p7vyB2GPegD/Yw3AuCEbFPqCsLtvhEUeYNQAenun6waftJYSVrb19tm0T+iOwuQeWAffcsm7oyjYUrrvsCwmNZLQV1BKsIPcZ+OxUu53LKdxslRsOF3XjVgGxnsI8sAOwMc6Vrz/YHuF7mtdMegv6lmZO5q5WB9NXzz4RMc6/YOOmkM33fbq6g3J9d/5q50b9YJsZHjedSw3f29Mz99Tl1u4hw0u3rNZl/fWfq8sW3+WecZpONQXZIvHRshBmZVSTwWbAAVltxOA7MsutgD/ZL8VD9+LH7NJ6Z48lVtIerBDzkxEKa6R4OUW7LWXMvEtBBO2l9L70RQFe0Sp0suIh8U9C84ZWXJd90Ip3d9ZTsGxfvSSgh3JD11zzTWLJso7rP5zKLP8Xn+9vxUjR9KrctDrf7zl4sFj6Yv1sY7qfT+68eUXskTlBRf0NuQWd3O9cI/ILdgb0/P2ity8vXWDcLy4t/arZdmqBeZZpPw+8kLNJt8Cyg9fyS1WkKAhmOhaMNx0O8Z0474ofbnm5u1D8Eae67q7gEyRO2oEO7QjMTCHwMZtR0jce4rimEjGZ8U1lCWl3DcCXzCqYbzB4CJvKeMfsZkvWfsfTF+yAab8wjJehztW5fo7q/ShlorB48kVuq95+W9/+7all0AcUbku8Vc3XAmLeQzN3wNDfQ3wNp8Z6ivPbvlcHjIpfx4FHNhOG/VhMADJZDK0jWwLNF+DQsHOtC2YBzQAVkBzCbc5B4tzhK3+g4L9dDoS4a0/gIv3jiE+e39YWfI+YFkbzwtZB/bAexh4u65r5tyHccLpv/2ZpS1m1l9H5dChnatyj3VUDh7fu1EfVKt+ek981QsQ5/LE6k+e1VMHH/UYmrenKYfj/Iuyl/8rhik7neb25zM9yQGs2JJKpR60BgBqMKPogQJD123W7a0RGzHC6MX34Us7RchQsD9qmYQaSRuXndmW+DuII0ywi+KeVT9h1EVK717LKbS/BT4vZ3n/Ab6oZAHk+1sCQMy5vvZlXzi4c4U+uKty6LFdlYMDu6v1o51rvn93tspvy5eVlVW3nn/mBTs3HCjviujy3Y3D83u3DMM4/xm9W4whNnGS8gfQ+odGsKWUtS0tLRrm/yeTybA226xT/KBxgw+fgvu/Y8d2rCkMlyAtaKfCW30TCbb1JoDVk9u3b3+pvT9MsINRz5Zjwywej29ADtYgjqtMPC8xDLybm6/94QSZN9z2Jy98fn/78qOHO5brvs4VJw/vrNT9nWu+evdWvy2/dWuZadO/sH1j/Qv2MD1v16aR8mzjCIzzz+vdclM+flL+PIr8AbZRpZTp1lbT/h0CAxBUjtl8jAIKAgn5lDJRcvUfpeJRZIH34W/cFzoS3TvzkE+TA5Qlz/NSlkfJ6b+w1BmE8bzE9YAnzEvC4b/D6WVXwdz/Y61LBp9IL9XHW5dBW94Yh63Qlrft+XPbY9nzbmjU83dHB+F9/gXZpu4A+tPFCAeyPIVDBC+lfD8YgEQiMZhKpWZ1bR9Q1nwfBpyT0g1d/QcFe/SllikvIz5qIIzwId8pFMVsCGKUzPM8/EbiuGYSGk7YA3N4OQsyHsYJDcDB9PKboKf/ROuF+kTLJeihleHwIII7Z+eV3158XaM+652NemG2IWXPQ5pI+RFS8R7Bwye9M5kMFMhJawCggGDlV7Pw40zbo5KH7YuFMPC22oS1tlLe14GDUt5wMQ/bZ5JnBl/VtazHNSWKy2CW/DZK9ra3ve35SilYKxA4mfZ/kLdV/JGkBINb8kMiY5AcaKv8yfH0hfqJlgvegxcCym+e+8Lsqy84K/uqp6DmP/+2V/+TDQfsSfkRWom9EVApZQw8AOgDSKVSJ+2qr0MzdW9n4kENNOGGym9rIxEQHMRlBAjWv1PKO2oVHVfFHcMnsET2PlgtGCM4HfaBiuRKfxk5f/XjMP7gaYEBUNLFYbkwJTXnHkqvXnGgvUofS7/0ZuQYUP4ydP/nd19+9dl7X6HP6HLeYMLRMB/imtLewB5t+3dbAzANan0zHAkdks9wezqei/e4lPJvLKkxNTYK9uj3Ea4urvXDfvs1n/vZsLimVBIzNBByklK+DwzAROXWopK6VUndkvQkZBebWMGso/u/P10tD2Sq88ofUqMb2T2ra+P7ztxd+48mDlL+IMqnd+y6bl0ymbxZKe+jSsmPnbpNfUypZ7NNJe3eh0Z761NK5af9jlF+S86cSya9v1VKfi6ZhC8llYz7I1J6/yFl4mXBe59eCczs0FLKqzzPc6WU1yaTye2hm5fcnlLuDlhT0eY2zAMwlx7OVFcGiISHu2bJorM7Ll1pw4WVYSAKOpyIQDjgie6YPdcmEpxnwuWZ3DN7aJ6anExUhqcmRTPwqWbVVzs7EIDO6s26n1NV1qmymGp8M1A8Jk3yXGAKTQLYT7SFuPNhkU+V5VTDhT2DzhEBIkAEiAARIAJEgAgQASJABIgAEZjpBKhd7JcgcCAWM12ai9IPBXrGMyhY6Mh7NsLwbO4tysK4n89F3BAH5DH4V/w7eC3s+LlIR1i8pc492zIJizcsTsjXXzpvYWmbNudKKRBAgmtlNTU1ZkHNCVIMYYMCBsfB32G34vXisGGFg2HD4sFzYffhNdiHXS8+Z/Jrb4JrYc/Fe+ZUVdlVZcLDBZ8dFk/wuokzwBmfEQwDx8Ws4FwwzWN+Q3ybN2+G9+SLwxTHG7wv+IxS6Sj+YAaECz6j1H3wnOC1MC7B68XpDLsWfG4wfD7uJUuWLKqtrT2fMRb84GhYXHh//l48Mdv2JoOMsTRj7K6amprFNoPmfE1NzUsYY3dzHo3DecbYm4VwMjbMGOAAVQjnp5xHzWevHMeJCeH8lrGI+Z4e59GtnDv3CuHczzl/kHP+KOf8N5zz8zZt2nSREOK3nHMze0tERR1ndb8QrO4Bzuog7EOwRaPRdfBszvl/CyEgnns557/gnH+Dc95ZW1v7PJu24kI1vxmLdjMefcjhkfsZi/zCYZGPRyKRJcH8MLax0uER/P67uU+I6Ds5d74JAmTDGsEXwnE5Z9+PRqPmVV/G2N8xxjaGpGFeNBpNRKPRcnutzHH4ex2HP+hvzv2MsQGHR8zqw1FW2xrl0bcEn4UKE4lEVjss+utaXlvDWOTtDos85bDIAw6L/ozxyH2MRx5zWLQ/Gt1sJiAxVns95/yEEEJzzg86joOzHIsZAdcPCCHMF5DAqHHO73Acp3h5blPunPMWzvl3gIkQ4mtCiCNQhkKInwohHuGcHxZC/BBkinP+MVvmUI73QVjOuZnRJ4RICiH2CyEe5pz/Wgjxvmg0+nyb97lCOJ9wWO0DjG9+iPHN9zls81GHb8byMeXgOBvXMhbtDeFuTjHGuGCxLwteNyR4nY7FYrB9QQhhPj3miPo38Fjd/U6s7n4m6h9kov5RLup+WVNT88JScdrzs2PnOM6vGGO/CeTGgI1Go1sZYxqUF64xFr1VCKYZq8VFG0EYjLHgPLLFv8bMt9Q4d/bC72g0ar4VyDn7GPzmnN3BOf+gFYpb/HjZP4CAMsZ8Y+HE9kBBMVb3cc5jIJQf4Zy/H4QSjACEBUGzcXwehMcKOLzAM8YwBfI0x2GRRxiPHnd45D0Oi/6E8ahmLPJRG8YXJh79EuPRQ/YcGsjtftqjV2F8jEVeB+ccx/G/GmsME7tbCGeHDQPpMHFaA/u/eC8YKsfhg4zxhxgTtzuO8yHHcT7NWMQsGuLw6BcZjw6DYQzEZfLlOLXNkG4wzo5T+zcOi3yIsci/wTmHR37MWPQmMCQgvIxFbofznPN3cM7fKoT4Hef8UzbOMQYAFJlznuOcmzn5YNQs088EwufvsYb3e3CNc36L3X5h7/kwlBdjLMEYu8Ceu0cIcTsYFSHEpxlj5iMyQoifcM6f4pzfJoT4FYQVgpm3JxmruRgYM7b5bsY2387Y5jscXvspxjb63+vL8418HcrWphN2+XRGo9HdIEuc1R3gXNzCeaxNCPFBMAJwDIFZrO7feaxes1j9HU5d3QdZrO5jTNTBq8mlZCnwqJl7aCCBtQUldxzHfPraCq3JuC0Ubd3HMiHYt2IxDgX0qBCXvQiyju5vLMazsRgfqqryV2URgn8TwiEeIdjDnDvfxt/BvWB1HxK8bhhdM85j3+Qsti8YBo+5U9cMQoLeQP485//qC494jT2HhWfyCTW9VZJ87eGwyB8cFvkyxgE1KoRhPPq7gGcATZ9zhGB/4px9HsJyHqn1BZOl8V7Hca7wjYRjajbkwjlfBgIoeCy/1r3jxGK+gYu9Ge/HvV9jOg9CXEI4sECGMUJoTDh3Psk5ewjDwx6NL+eRejy/efPmpTY9+bfmhBAvCjQvMCgauYjlZwzYqHe21f4GA2Jep8V7gQcYCyHETowE9qDYUPMXnXuNr2z8VcHzcLx+/fpzbW1s0ug4ziV+WPbPcB3SAPIGbIvvxbRw7jRbVk+gJ1ZgxXsgD5zHzNeYgnEwxtZEIvXG+2Oi7iAX9fnyCYabzcdGQaLR6JVgAKLRaLHiQAH8nHMOH2ksAyMgBDshBP+pNQJfC8KJxdi3heA/gnMgxEKwk0KwT8BvUCYrjHvwOtQ4qCSCx+7nPPY/eE3wuj9yXmfe9a6oqFgIG7rPnMX+DWoMuB/CYxyj7mi9FVhsopjaF604Y5E3g3JHIhEG9znOphj8jrJaswZ9bW3tCsajQ4xFPsd45PcRFvnbsfGD4jknOa+t4dx5UgjnQ3AdhU0I58s2j9+0582Oc/5NvwaKGeMBJwWr2+kbALYGfkP+sPll4/+DEM5PihTYGDIhWB/nzr/AfciAc+c9ENa6rFahIy+Hc0I430J2JkHj/xlO4NIDP8dx1kMQIcS7bM18r+XqfyjTbwpe7isWR4NzhnX1nwIjAPdjfoQQN4FSo0GF8qqurjZvRwohmuCaEOKv4Z7R5sEb/d/O6/00sHeBrFnFnoNyUFD+zSuFYEOcs89x7jzlOI6Jx8ZVY9N4B/y2f5BX2LByKItG6ytM7S/quiAMsII0olzhjbNxbwrecZw9AAo6R2wmjQCBMFmAxhozxl4BhQG1H+fs/dYI7IZ7oNa3Cv9O+A1tLrgei3HTdyCE8/e+MLIhIZwnrGA9DhbfPAdcNGulsXbkLDbCeWyA89jjnMVOwHmIW/C6PiEEWmtQClOYfg1smhHbbT5M/vLXeeTdxgNgkf9iLPIV/zj6MxRGxiN3OSx6L9RuDovkHB4xtQYIHcTnOM4rOXdGOHcGQdntMwwrISLMGoWfCcF+jx4T9Etwzkc4q7uXs9hX7D1lnMU+Z43CMcHroD08yDk3BgL6FYAVsOGc3WmPTTODMVYJvxmLmpWKMG+cs19A/wvGD3soE87ZXRCec34PNq+CYeyxMSzwfCHE8YJBM+74f4BBADkQQvRhDcs5B5kZhmdgfJFIZIOVF/xAjOHPOb/TKvkxIcRALBYbFkKYikEI0QXXYrHYh6GZZ8N9EeMUgt3py5xzQgh23M+L82G8zrnza8gjeLGWi5FHuD7qeRmPEJpKNnxe6eE3GhGH110NBoDH6v7IY3VHmKg7wUXdoc2x2ERrE2ISZv7ecZzvQudNICeoUK+EAoWOP7gmBLtOCDYClhGUIhZj9/hK7jQKUVvlGwTfi+Cct8HvQm3CbvcLyLkO+gY45zcKIUw7DzqlQBlE1HfdTRsNfrPY+wSL3cSd2G3cid0MwgZKYRSH806b3nlYkIyxdiuANfaaUU7MF3SSOSxylLHInf4W7UVFBS8ADAJ4ARDeYdGfOyyCzRWjIJBvztmAEM4jWPPis4VwviSE813Oo2+BfEK+fRfcKM5OwWL/Bc0aiNt4R6zuKGd1v2ZMXOe3S0172XgcnLPPwDMgLNSavmEBpYf2sPN6nyPLvxUHTTE4JwR7l80rlJ/JOygGeAtWgcEQNNswY9iA0nPOH8cOQF+hTKehWRKdMaYs26/D/Zzzb3POsU8DFd2DMGAI7DOgNn2+EOJPQoi7YrHYXiHELbFY7AOB9j905g5aIwAVwzZkaj3Ok7EY+yHnTqcQ7HrOndsKncpOD9T+BaMEzSbHVAx+WfH+QBoxSbjPVxxMxN4HBsCJ1d8MG4/VvZvH6rOYDrxhVu7B3RFCDEHPq83gPHR9oOMIChQ7ojjn3+OcGzcdwgohqq1Q3Dfak/9uqBHQiwBBAoEK1iac8/8XBhFcTYgHe5vhXiHEE2FhGfM7C8F1hOuoiDY9MCpwT9F9RnkhXfAM6Iwrug41u6nhOOcnoTcbaqzRNu+TtsbL99pD+9nm9waIAz0DMBr2/Gtra2svtMfNwAs6tSAsCCKMXNjjGmPEnDpUxmCS5tjec2xeGM/DxgnpguYPtLEhXyZvnPOr4LoQwrjNyDwYKWPsdTYO4BNUfnPsOM5aG4dpPoG3A78ZY7goSr5GhdofyhaMODwjIC//Dp2M+NvmdYuN9++D6YFj22Q4yTm/gXP+SVvm+VqaMYbNDPOV4OD9juNsAqMHbj8oPYxGgFcJowmQP6gs7HPNiIb1lIAX5Df/DJtGGEkyTdfgM2b7sREe6KG3oHDBRCMQUIhCiKOc8+8DiEBhmQ40VLyAQoIA/sBCG1ObgGLDM6CXGK7Dvda6mprD9uabfgYoHHBDeaGnGssBa5kPgLuMJ3EvhHinfQZ2NGEhm70Q4jVw3XGcqL0H4jNKZIci9wkhPsQ5/yK0Ye1QFqQZPwkOzZpX22cYgURBhxENMBYQr+X2GBgQUBLHccyXZqHHXAjxJQhjalMzwlGoxTEf0Wi0Ap4BXOEcGhnG2G44bzczaoHPF0JcD+exJoT7YGgVvRmMGxQMjBL+tnvDlTF2rX0ut/eDlzaI7Xg4B5WFHaYz6QAjYeMwimUNl8kj1p6c82xx2uw9wMF0Oo5WKluAs82bWRTUpsHcC54U/LYs5kLcMJTsdyqzO8D7AnmB/iqbB+Md2WHJQ8gQn2vjPg/2mzZtejHcwznvCV4vMpJFl2bHT2MAQEAt+N9C+xJqDxhnhlrQgjGdPFATWLhmmM5aUWMscKQAOnsADRYmNAPgd0D5cPwZlBINELiIUACmnyFQG4OiX8oYa4DOPegttnHDvIEHwQOAPglQlEBasVlg4rbFhAbgJhDoQGcYGpSb4fmgeDa82WGHIs5LsM+GZgt4RC/GsOAV2KEzXHQS8g81Yb7pZO/9NQ6vmZ5yFoPhNqgdmeM4jdFo9DIIB0OKRekxjG0cMG4N8V5rn2/yAIYX5kTYc2YHHY8w5g/5sl6e8bKgk82GQwOJZfiv4AGhwoPhR+MfDA/j5hDO1rZmFAiu+30TJm2mQxUNgJ0fAEOP0EELQtQEnqO9p9XmxywEYj3MQWAK160nlh9FwppbCL85iZ2KNn1goF4G8Y3OF7na3g/DivD7MzASA309o57GSuutmq8PoWfEGOuCpgvIG8xfCTMa+JxZtxdCfBRABTdbyCgsUMAdFqaxnFh7IgwhxM+EEGbxRc65tHGZ3naY8AG/A50xoKBGANF95Zy/yRaaudcOMWGahmAsGe63bjqeN3uYDBTs/cU0Bfd2NAMX+TTPxk5DIYQLYa3QmmvwLJuHvCtux6txroRRHDB6nPM/4PClzQO4tP9pn2+M0agh2QeGwV7fZ/o8oJ/DcuecmwlAjLGPCyFwDoKNwnfZbRPk4Wg06uAFMIwQB4y5w7mA4mUwbtxzznH4E2/H/Vx45qiFN99A9Cd0Ceiow4k1xtBg0wLa6YHmHJbjNngOdP5ipDZtR/H5uMemA3TkQtlheHT5wduBc1YGjKJivvymiZmXsg3CBMuMMXYxPINzboYUbT6gOZnnjMfYr2WNAdwzAtcYeIlC7EdDiGmb7fs5UEuDGwRCYmshtO5G0MHCB9znIA+8fjG6oNCsgB5nLDQYbwXrH7wJPQDo1IOaHGfwwXPAKIAwgIKOts2uFEKY4ScrUHXwGzySaDS6GfsnbNzBmj/4OMhfE05IwmeDxYdaIxgwcGzuCc7qgzyAdxQIA4axAdJhzxkW8Bw7HJcPCrW9reXnQt7guZA/m89XYj5smsxsx/zN/gFyXoOs4DQIKuQN+h5s+DwDYAflCWP16GEUxYk/50F5IR+I09aEOAMQw+F+bmAOBhq4ZXAPljkEhBoX8hjMq/XasMavxZl4GDEYkNFmWA00EyFfyAXLDMoD4sTwRXvoP0HO+UuQLuggtvL9FvQwIAB4nJAmLAs4hufnbz7ND4zQTYFBXuiwoKZwT1iQYDxh1yc6Z2qiiQL8ma9h2nEPjwseT+Xxk4V/ttenUp6TPWMq+XiuwzzTNE1030TXnuv0z4j4QIHA1YMNjosBwe+JBCgYvjhs8e8gELgWVF78Dc8KbnhP8BwcQ/ip/IWFnShdEGfxPcW/MQycD/5BvMXpCt4Lx8EtyHuyNBXHC88Nxh1MR7BMIcxEf8VxFP8uvrc4HaXSDfEEt2Bew54RPBc8xueXeg5eD7sHzpWSbYgP0gRhghvGR3siQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJABIgAESACRIAIEAEiQASIABEgAkSACBABIkAEiAARIAJEgAgQASJQmsD/BxGqCacUyvr0AAAAAElFTkSuQmCC"/></defs></svg>';
                                        break;
                                    case 'crypto':
                                        icon = '<i class="ti ti-currency-bitcoin me-1"></i>';
                                        break;
                                    default:
                                        icon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="39" height="26" viewBox="0 0 39 26" fill="none"><rect x="0.789745" y="1.31843" width="36.8377" height="24.0988" rx="5.16402" stroke="#ECEFF2" stroke-width="1.14756"/><rect x="1.59497" y="1.89221" width="35.6901" height="22.9512" rx="9.18048" fill="#FBFBFB"/><path d="M11.2775 19.4883H27.1397V21.0184H11.2775V19.4883ZM12.8637 13.368H14.4499V18.7233H12.8637V13.368ZM16.8293 13.368H18.4155V18.7233H16.8293V13.368ZM20.0017 13.368H21.5879V18.7233H20.0017V13.368ZM23.9673 13.368H25.5535V18.7233H23.9673V13.368ZM11.2775 9.54279L19.2086 5.71759L27.1397 9.54279V12.603H11.2775V9.54279ZM19.2086 10.3078C19.6466 10.3078 20.0017 9.96531 20.0017 9.54279C20.0017 9.12027 19.6466 8.77775 19.2086 8.77775C18.7706 8.77775 18.4155 9.12027 18.4155 9.54279C18.4155 9.96531 18.7706 10.3078 19.2086 10.3078Z" fill="#4B5563"/></svg>';
                                }
                                return icon;
                            }
                        },
                        {
                            data: 'amount',
                            name: 'amount',
                            render: function(data, type, full) {
                                var amount = data != null ? parseFloat(data) : null;
                                if (amount != null) {
                                    return amount.toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                                return '-';
                            }
                        },
                        {
                            data: 'payment_amount',
                            name: 'payment_amount',
                            render: function(data, type, full) {
                                var pAmount = data != null ? parseFloat(data) : null;
                                if (pAmount != null) {
                                    var formattedAmount = '₹' + pAmount.toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                    return '<span class="badge bg-label-success "style="font-weight:100">' +
                                        formattedAmount +
                                        '</span>';
                                }
                                return '-';
                            }
                        },
                        {
                            data: 'utr',
                            name: 'utr',
                            defaultContent: '-'
                        },
                        {
                            data: 'payment_from',
                            name: 'payment_from',
                            defaultContent: '-'
                        },
                        {
                            data: 'account_upi',
                            name: 'account_upi',
                            defaultContent: '-'
                        },
                        {
                            data: 'image',
                            name: 'image',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full) {
                                if (!data || data === '<span class="text-muted">-</span>') {
                                    return '<span class="text-muted">-</span>';
                                }
                                // Extract image URL from the button HTML
                                var imageUrl = '';
                                var match = data.match(/data-image="([^"]+)"/);
                                if (match && match[1]) {
                                    imageUrl = match[1];
                                    return '<img src="' + imageUrl +
                                        '" alt="Screenshot" class="screenshot-thumbnail view-screenshot-btn" data-image="' +
                                        imageUrl +
                                        '" style="width: 39px; height: 39px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #e0e0e0; transition: all 0.2s;" onmouseover="this.style.transform=\'scale(1.1)\'; this.style.borderColor=\'#007bff\';" onmouseout="this.style.transform=\'scale(1)\'; this.style.borderColor=\'#e0e0e0\';">';
                                }
                                return data;
                            }
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function(data) {
                                if (!data) return '-';
                                var date = new Date(data);
                                return (
                                    date.toLocaleDateString() +
                                    '<br><small class="text-muted">' + date.toLocaleTimeString(
                                        [], {
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit'
                                        }) + '</small>'
                                );
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full) {
                                return data || '<span class="text-muted">No Actions</span>';
                            }
                        }
                    ],
                    order: [
                        [11, 'desc']
                    ],
                    scrollX: true,
                    responsive: false,
                    autoWidth: false,
                    columnDefs: [{
                        targets: '_all',
                        className: 'text-start'
                    }]
                });
            }

            function triggerReload() {
                updateCacheStatus('PENDING');
                if (dt_assigned_requests) dt_assigned_requests.ajax.reload(null, false);
            }

            // Filter controls triggering DataTable reload
            $('#request_type_filter, #mode_filter, #status_filter').on('change', triggerReload);
            $('#start_date, #end_date').on('change', triggerReload);
            $('#search_term').on('keyup', function(e) {
                if (e.key === 'Enter') triggerReload();
            });

            // Top button handlers
            $('#pendingRequestsBtn').on('click', function() {
                $('#request_type_filter').val('pending').trigger('change');
            });

            $('#rejectedRequestsBtn').on('click', function() {
                $('#request_type_filter').val('rejected').trigger('change');
            });

            // Export full dataset (server-side) using current filters (top button)
            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                var params = {};
                var mode = $('#mode_filter').val();
                var status = $('#status_filter').val();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var search_term = $('#search_term').val();

                // Handle request type filter for export
                var requestType = $('#request_type_filter').val();
                if (requestType === 'pending') {
                    params.status = 'pending';
                } else if (requestType === 'rejected') {
                    params.status = 'rejected';
                } else if (status && status !== 'all') {
                    params.status = status;
                }

                if (mode && mode !== 'all') params.mode = mode;
                if (start_date) params.start_date = start_date;
                if (end_date) params.end_date = end_date;
                if (search_term) params.search_term = search_term;

                var query = Object.keys(params).map(function(k) {
                    return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
                }).join('&');

                var url = '{{ route('requests.assigned.export') }}' + (query ? ('?' + query) : '');
                window.location = url;
            });

            // Auto-reload handler
            $('#auto_reload').on('change', function() {
                var val = parseInt($(this).val(), 10) || 0;
                if (autoReloadTimer) {
                    clearInterval(autoReloadTimer);
                    autoReloadTimer = null;
                }
                if (val > 0) {
                    autoReloadTimer = setInterval(function() {
                        if ($('.datatables-assigned-requests').is(':visible')) {
                            triggerReload();
                        }
                    }, val * 1000);
                }
            });

            // DataTable search handler
            $('#dt_search').on('keyup', function() {
                if (dt_assigned_requests) {
                    dt_assigned_requests.search(this.value).draw();
                }
            });

            // Accept assigned request
            $(document).on('click', '.assigned-accept-request', function() {
                const requestId = $(this).data('id');
                Swal.fire({
                    title: 'Accept Payment?',
                    text: 'Do you want to accept this payment request?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, accept it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: '/app/payment/requests/accept-payment/' + requestId,
                        type: 'POST',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            dt_assigned_requests.ajax.reload();
                            Swal.fire('Accepted!', response.message ||
                                'Deposit request accepted', 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Error accepting payment request', 'error');
                        }
                    });
                });
            });

            // Reject assigned request
            $(document).on('click', '.assigned-reject-request', function() {
                const requestId = $(this).data('id');
                Swal.fire({
                    title: 'Reject Payment?',
                    text: 'Do you want to reject this payment request?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, reject it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: '/app/payment/requests/reject/' + requestId,
                        type: 'POST',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            dt_assigned_requests.ajax.reload();
                            Swal.fire('Rejected!', response.message ||
                                'Deposit request rejected', 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Error rejecting payment request', 'error');
                        }
                    });
                });
            });

            // Edit/Update Transaction - Open Modal
            $(document).on('click', '.assigned-edit-request', function() {
                const requestId = $(this).data('id');

                // Fetch request data
                $.ajax({
                    url: '/app/payment/requests/' + requestId + '/get',
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        const data = response.data;

                        // Populate modal fields
                        $('#update_request_id').val(data.id);
                        $('#update_trans_id').val(data.trans_id || '-');
                        $('#update_mode').val((data.mode || '-').toUpperCase());

                        // Format amount with 2 decimals
                        const amount = parseFloat(data.amount || 0).toFixed(2);
                        $('#update_amount').val('₹ ' + amount);

                        $('#update_payment_from').val(data.payment_from || '-');

                        // Format status with proper capitalization
                        const status = (data.status || 'pending').charAt(0).toUpperCase() + (
                            data.status || 'pending').slice(1);
                        $('#update_current_status').val(status);

                        // Populate editable fields
                        $('#update_utr').val(data.utr || '');

                        // Set payment amount - if exists use it, otherwise use the requested amount as default
                        const paymentAmount = data.payment_amount || data.amount || '';
                        $('#update_payment_amount').val(paymentAmount);

                        // Show modal
                        $('#updateTransactionModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message ||
                            'Error loading request data', 'error');
                    }
                });
            });

            // Approve Transaction
            $('#approveTransactionBtn').on('click', function() {
                const requestId = $('#update_request_id').val();
                const utr = $('#update_utr').val().trim();
                const paymentAmount = $('#update_payment_amount').val();

                if (!utr) {
                    Swal.fire('Required!', 'Please enter UTR number', 'warning');
                    return;
                }

                if (!paymentAmount || parseFloat(paymentAmount) <= 0) {
                    Swal.fire('Invalid!', 'Please enter valid payment amount', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Approve Transaction?',
                    text: 'Do you want to approve this transaction?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, approve it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const btn = $('#approveTransactionBtn');
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Approving...'
                    );

                    $.ajax({
                        url: '/app/payment/requests/' + requestId + '/update-and-approve',
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            utr: utr,
                            payment_amount: paymentAmount
                        },
                        success: function(response) {
                            $('#updateTransactionModal').modal('hide');
                            dt_assigned_requests.ajax.reload();
                            Swal.fire('Approved!', response.message ||
                                'Transaction approved successfully', 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Error approving transaction', 'error');
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(
                                '<i class="ti ti-check me-1"></i>Approve');
                        }
                    });
                });
            });

            // Cancel Transaction
            $('#cancelTransactionBtn').on('click', function() {
                const requestId = $('#update_request_id').val();

                Swal.fire({
                    title: 'Cancel Transaction?',
                    text: 'Do you want to reject this transaction?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, reject it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const btn = $('#cancelTransactionBtn');
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Cancelling...'
                    );

                    $.ajax({
                        url: '/app/payment/requests/reject/' + requestId,
                        type: 'POST',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            $('#updateTransactionModal').modal('hide');
                            dt_assigned_requests.ajax.reload();
                            Swal.fire('Rejected!', response.message ||
                                'Transaction cancelled successfully', 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Error cancelling transaction', 'error');
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(
                                '<i class="ti ti-x me-1"></i>Cancel Transaction');
                        }
                    });
                });
            });

            // View Screenshot in Modal Handler
            $(document).on('click', '.view-screenshot-btn', function(e) {
                e.preventDefault();
                var imageUrl = $(this).data('image');
                console.log('Screenshot URL:', imageUrl);
                $('#previewImage').attr('src', imageUrl);
                $('#downloadImageBtn').attr('href', imageUrl);
                var modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                modal.show();
            });

            // Change Screenshot Button
            $(document).on('click', '#change_screenshot_btn', function() {
                $('#current_screenshot_section').hide();
                $('#upload_screenshot_section').show();
            });

            // Preview new screenshot before upload in update modal
            $('#update_screenshot_file').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        Swal.fire('File Too Large!', 'File size must be less than 2MB', 'error');
                        $(this).val('');
                        return;
                    }

                    // Validate file type
                    if (!file.type.match('image.*')) {
                        Swal.fire('Invalid File!', 'Please upload an image file (JPG, PNG, JPEG)', 'error');
                        $(this).val('');
                        return;
                    }

                    // Show preview
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#update_preview_img').attr('src', e.target.result);
                        $('#update_screenshot_preview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#update_screenshot_preview').hide();
                }
            });
        });
    </script>
@endsection

@section('content')
    <style>
        #DataTables_Table_0_filter,
        #DataTables_Table_0_length {
            display: none;
        }

        .badge {
            font-size: 15px !important;
        }

        /* Rounded corners and card effect for the table */
        .table {
            border-radius: 16px !important;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(153, 164, 188, 0.13);
            background: #fff;
            /* Base card background */
        }

        /* Header styles */
        .table th {
            /* background: linear-gradient(90deg, #f3e9fa 0%, #e8f9e9 100%); */
            color: #352e5a;
            font-size: 15px;
            font-weight: 600;
            border: none;
        }

        /* Zebra striping for rows */
        /* .table-striped>tbody>tr:nth-of-type(odd) {
                                                                                                                                                                                                                                                                                                    background-color: #f8fafc;
                                                                                                                                                                                                                                                                                                }

                                                                                                                                                                                                                                                                                                .table-striped>tbody>tr:nth-of-type(even) {
                                                                                                                                                                                                                                                                                                    background-color: #f3f4f8;
                                                                                                                                                                                                                                                                                                } */

        /* Hover effect on rows */
        .table tbody tr:hover {
            background: #e0f7fa !important;
            box-shadow: 0 1px 6px rgba(60, 120, 200, 0.07);
            transition: background 0.2s, box-shadow 0.2s;
        }

        /* Cell padding and font */
        .table th,
        .table td {
            font-size: 15px;
            padding: 0.5rem 0.5rem;
            /* font-size: 0.875rem; */
            vertical-align: middle !important;
            white-space: nowrap;
            text-align: left !important;
        }

        /* Ensure consistent alignment for all screen sizes */
        .datatables-assigned-requests th,
        .datatables-assigned-requests td {
            text-align: left !important;
        }

        /* Bolder important cells, like status or actions */
        .table td .fw-medium,
        .table td .text-success,
        .table td .text-danger,
        .table td .text-warning {
            font-weight: 600;
            font-size: 14px;
        }

        .table td small {
            /* font-size: 0.92em; */
            color: #8678c5;
        }

        /* Rounded pagination for DataTables */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            margin: 0 3px;
            background: #f3e9fa !important;
            color: #352e5a !important;
            border: none !important;
            transition: background 0.18s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #b3e2b0 !important;
            color: #283593 !important;
        }

        /* Search and filter boxes styling */
        .dataTables_filter input,
        .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #d1c4e9;
            padding: 0.4em 1em;
            /* font-size: 1em; */
            background: #fafaff;
            margin-right: 6px;
        }

        /* Make table horizontally scrollable on smaller viewports so icons don't get hidden */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Table responsive wrapper */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .datatables-assigned-requests {
            width: 100% !important;
        }

        @media (max-width: 992px) {}
    </style>
    <section class="app-assigned-requests-list">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title"> Deposit Requests</h5>
                    <span class="cache-status-indicator ms-2" title="Cache status">
                        <span class="cache-dot cache-pending"></span>
                        <small class="cache-text">LOADING</small>
                        <small class="load-time ms-1"></small>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button id="pendingRequestsBtn" class="btn btn-success">Pending Requests</button>
                    <button id="rejectedRequestsBtn" class="btn btn-danger">Rejected Requests</button>
                    <a id="exportExcelBtn" style="color:#fff" class="btn btn-primary">Export Excel</a>
                </div>
            </div>

            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select id="auto_reload" class="form-select form-select-sm" style="width: auto;">
                        <option value="0">Auto-reload: Off</option>
                        <option value="5">5s</option>
                        <option value="10">10s</option>
                        <option value="15">15s</option>
                        <option value="20">20s</option>
                        <option value="30">30s</option>
                        <option value="60">60s</option>
                    </select>
                    <select id="mode_filter" class="form-select form-select-sm" style="width: auto;">
                        <option value="all">All</option>
                        <option value="bank">Bank</option>
                        <option value="upi">UPI</option>
                        <option value="crypto">Crypto</option>
                    </select>

                    <select id="request_type_filter" class="form-select form-select-sm" style="width: auto;">
                        <option value="all">All </option>
                        <option value="pending" selected>Pending </option>
                        <option value="accepted">Approved</option>
                        <option value="progress">Progress</option>
                        <option value="rejected">Rejected </option>
                    </select>
                    <label for="" class="mb-0 small">from:</label>
                    <input type="date" id="start_date" class="form-control form-control-sm" style="width: auto;"
                        placeholder="Start date" />
                    <label for="" class="mb-0 small">to:</label>

                    <input type="date" id="end_date" class="form-control form-control-sm" style="width: auto;"
                        placeholder="End date" />
                    {{-- <input type="text" id="search_term" class="form-control" style="width: 200px;"
                        placeholder="Search UTR/Trans ID" /> --}}

                    <input type="search" id="dt_search" class="form-control form-control-sm" style="width: 180px;"
                        placeholder="Search UTR/Trans ID" />
                    {{-- <a href="{{ route('requests.add') }}" class="btn btn-primary">Add Payment Request</a> --}}

                </div>
            </div>
            <div class="card-body border-bottom pt-0">
                <div class="table">
                    <table class="table datatables-assigned-requests">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Trans. ID</th>
                                <th>Approver Name</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>Payment Amount</th>
                                <th>UTR</th>
                                <th>Payment From</th>
                                <th>Account/UPI</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>


        <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content bg-transparent">
                    <div class="modal-body text-center p-0">
                        <img id="previewImage" src="" alt="Screenshot" class=""
                            style="max-height: auto; width: 300px;">
                    </div>
                </div>
            </div>
        </div>


        <!-- Image Preview Modal -->
        {{-- <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content"
                    style="background: rgba(255, 255, 255, 0.98); border-radius: 16px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); overflow: hidden;">
                    <div class="modal-body text-center p-0" style="position: relative;">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            style="position: absolute; top: 15px; right: 15px; z-index: 10; background: rgba(255, 255, 255, 0.9); border-radius: 50%; padding: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); opacity: 0.8; transition: all 0.2s;"
                            onmouseover="this.style.opacity='1'; this.style.transform='scale(1.1)';"
                            onmouseout="this.style.opacity='0.8'; this.style.transform='scale(1)';"></button>
                        <img id="previewImage" src="" alt="Payment Screenshot"
                            style="width: 100%; max-width: 500px; height: auto; display: block; border-radius: 16px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Update Transaction Modal -->
        <div class="modal fade" id="updateTransactionModal" tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-edit me-2"></i>Update Transaction
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="update_request_id">

                        <!-- Transaction Info (Read-only) -->
                        {{-- <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transaction ID</label>
                                <input type="text" class="form-control-plaintext" id="update_trans_id" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mode</label>
                                <input type="text" class="form-control-plaintext" id="update_mode" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Requested Amount</label>
                                <input type="text" class="form-control-plaintext" id="update_amount" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment From</label>
                                <input type="text" class="form-control-plaintext" id="update_payment_from" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Status</label>
                            <input type="text" class="form-control-plaintext" id="update_current_status" readonly>
                        </div>

                        <hr class="my-4"> --}}

                        <!-- Editable Fields -->
                        {{-- <h6 class="mb-3 text-primary">Update Payment Details</h6> --}}

                        <div class="mb-3">
                            <label for="update_utr" class="form-label fw-bold">UTR Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="update_utr" placeholder="Enter UTR number"
                                required>
                            <small class="form-text text-muted">Enter the unique transaction reference number</small>
                        </div>

                        <div class="mb-3">
                            <label for="update_payment_amount" class="form-label fw-bold">Payment Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="update_payment_amount"
                                placeholder="Enter actual payment amount" required>
                            <small class="form-text text-muted">Enter the actual amount received</small>
                        </div>

                        <!-- Screenshot Section -->
                        {{-- <div class="mb-3">
                            <label class="form-label fw-bold">Payment Screenshot</label>
                            <div id="current_screenshot_section" style="display: none;">
                                <div class="border rounded p-3 mb-2 text-center">
                                    <img id="current_screenshot_img" src="" alt="Current Screenshot"
                                        style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                                    <div class="mt-2">
                                        <a id="current_screenshot_link" href="" target="_blank"
                                            class="btn btn-sm btn-info me-2">
                                            <i class="ti ti-eye me-1"></i>View Full Size
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning" id="change_screenshot_btn">
                                            <i class="ti ti-refresh me-1"></i>Change Screenshot
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload_screenshot_section">
                                <input type="file" class="form-control" id="update_screenshot_file" accept="image/*">
                                <small class="text-muted">Max size: 2MB (JPG, PNG, JPEG)</small>
                                <div id="update_screenshot_preview" class="text-center mt-2" style="display: none;">
                                    <img src="" alt="Preview" id="update_preview_img"
                                        style="max-width: 100%; max-height: 250px; border-radius: 8px;">
                                </div>
                            </div>
                        </div> --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Close
                        </button>
                        {{-- <button type="button" class="btn btn-danger" id="cancelTransactionBtn">
                            <i class="ti ti-ban me-1"></i>Reject Transaction
                        </button> --}}
                        <button type="button" class="btn btn-success" id="approveTransactionBtn">
                            <i class="ti ti-check me-1"></i>Approve & Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

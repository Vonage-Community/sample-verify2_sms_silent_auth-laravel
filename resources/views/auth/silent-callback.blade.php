<x-guest-layout>

</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.hash.substring(1));

        if (params.has('error_description')) {
            window.location.href = window.location.origin + '/sms';
        }

        if (!params.has('request_id') || !params.has('code')) {
            window.location.href = window.location.origin + '/sms';
        } else {
            const request_id = params.get('request_id');
            const code = params.get('code');
            window.location.href = window.location.origin + '/silent-check?request_id=' + request_id + '&code=' + code;
        }
    })
</script>

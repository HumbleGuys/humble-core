@foreach ($rows as $row)
    {{ $row['value'] ?? '' }}
@endforeach

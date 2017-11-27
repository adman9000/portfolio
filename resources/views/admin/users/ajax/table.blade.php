 @if(isset($records))

        <table class='table table-bordered'>
            <thead><tr><th>Date Created</th><th>Date Modified</th><th>Email</th><th>Name</th><th></th></tr></thead>
            <tbody>

                @foreach ($records as $record)
                    <tr><td>{{ $record['created_at'] }}</td>
                    <td>{{ $record['updated_at'] }}</td>
                    <td>{{ $record['email'] }}</td>
                    <td>{{ $record['name'] }}</td>
                    <td>
                        <a data-toggle='modal-ajax' href="/admin/users/show/{{ $record['id'] }}" class='btn btn-info btn-sm'>View</a>
                        @can('edit users')
                        <a data-toggle='modal-ajax' href="/admin/users/edit/{{ $record['id'] }}" class='btn btn-primary btn-sm'>Edit</a>
                        <a href="/admin/users/permissions/{{ $record['id'] }}" class='btn btn-primary btn-sm'>Permissions</a>
                        @endcan
                    </td>
                    </tr>
                @endforeach

            </tbody>
        </table>

    @else

    <p>No Registered Users Found</p>

    @endif

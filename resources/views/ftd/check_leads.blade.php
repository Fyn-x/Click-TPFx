<form method="post" action="{{ route('ftd.check_leads_store') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" id="file_clients" name="file_clients" required><br>
    <input type="submit" name="submit">
</form>

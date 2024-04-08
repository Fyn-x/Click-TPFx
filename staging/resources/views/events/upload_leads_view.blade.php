<form method="post" action="{{ route('events.upload_leads') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" id="file_leads" name="file_leads" required><br>
    <input type="submit" name="submit">
</form>

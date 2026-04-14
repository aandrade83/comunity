<? 

require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
  $aa = "_en";



$pass = "ComiteVV";
echo $pass."<BR><BR>";
$pass = biencript($pass);
echo $pass;

exit;

  $data['aaa'] = 'aaaa';
  $data['aaa_en'] = 'eeee';

  echo $data['aaa'.$aa];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de Texto Enriquecido</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>
<body>
    <form id="post-form" action="https://lab.lacallecr.com/VV/apps/Forum/proccess/actions_test.php" method="POST">
        <input type="hidden" name="content" id="content">
        <input type="hidden" name="ac" id="ac" value="test">
        <input type="text" name="title" placeholder="Title" required>
        <div id="editor-container" style="height: 300px;"></div>
        <button type="submit">Guardar</button>
    </form>
    
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link', 'image'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }]
                ]
            }
        });

        document.getElementById('post-form').onsubmit = function() {
            var content = document.querySelector('input[name=content]');
            content.value = quill.root.innerHTML;
        };
    </script>
</body>
</html>

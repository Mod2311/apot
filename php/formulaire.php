<?php
if (!empty($_POST)) {
    // print_r($_POST); exit;
    foreach ($_POST as $key => $value) 
        $_POST[$key] = trim(strip_tags($value));
}

$fields = [
    'firstname' => [
          'type' => 'text',
          'require' => true,
          'placeholder' => 'Votre prénom',
          'label' => 'Prénom',
          'error' => 'Votre prénom ne semble pas valide (au moins 2 caractères)',
          'regex' => '^[A-Za-z -]{2,}$'
    ],
    'lastname' => [
          'type' => 'text',
          'require' => true,
          'placeholder' => 'Votre nom de famille',
          'label' => 'Nom',
          'error' => 'Votre nom de famille ne semble pas valide (au moins 2 caractères)',
          'regex' => '^[A-Za-z -]{2,}$'
    ],
    'pseudo' => [
          'type' => 'text',
          'require' => true,
          'placeholder' => 'Votre pseudonyme',
          'label' => 'Pseudo',
          'error' => 'Votre pseudonyme ne semble pas valide (au moins 2 caractères en minuscules)',
          'regex' => '^[a-z -]{2,}$'
    ],
    'alias' => [
          'type' => 'text',
          'placeholder' => 'Votre alias',
          'label' => 'Alias',
          'error' => 'Votre alias ne semble pas valide (au moins 2 caractères)',
          'regex' => '^[A-Za-z -]{0,}$'
    ],
    'email' => [
          'type' => 'email',
          'require' => true,
          'placeholder' => 'Votre email',
          'label' => 'Email',
          'error' => 'Votre email ne semble pas valide',
          'regex' => '^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$'
    ],
    'password' => [
          'type' => 'password',
          'require' => true,
          'placeholder' => 'Votre mot de passe',
          'label' => 'Mot de passe',
          'error' => 'Votre mot de passe ne semble pas valide',
          'regex' => '^[A-Za-z0-9-]{6,}$'
    ],
    'password2' => [
          'type' => 'password',
          'require' => true,
          'placeholder' => 'Confirmation mot de passe',
          'label' => 'Mot de passe',
          'error' => 'Les 2 mots de passe doivent correspondre !'
    ],
    'newsletter' => [
          'type' => 'checkbox',
          'label' => 'Newsletter',
          'value' => '1',
    ],
    'conditions' => [
          'type' => 'checkbox',
          'label' => "J'accepte les conditions générales de vente",
          'require' => true,
          'error' => 'Merci d\'accepter les conditions générales de vente!',
          'value' => '1',
    ]
];

function isFormValid() {
    global $fields;
    if (empty($_POST)) return false;
    foreach ($fields as $name => $value) {
        if (isset($value['require']) && $value['require'])
            if (!isFieldValid($name)) return false;
    }
    return true;
}

function isFieldValid($name) {
    if (empty($_POST)) return true;
    global $fields;
    if ($name == 'password2' && isFieldValid('password')) 
        return !empty($_POST['password2']) && $_POST['password'] == $_POST['password2'];

    if ($fields[$name]['type'] == 'checkbox' && isset($fields[$name]['require']) && $fields[$name]['require']) 
        return empty($_POST[$name]) ? false : $fields[$name]['value'] == $_POST[$name];

    if (empty($fields[$name]['regex'])) return true;
    return preg_match('/'.$fields[$name]['regex'].'/', $_POST[$name]) == 1;
}

function getCSSError($name) {
    return isFieldValid($name) ? '' : ' has-error';
}

function showErrorMsg($name) {
    return isFieldValid($name) ? ' hidden' : '';
}

function getField($name, $value) {
    $lines[] = '<div class="form-group'.getCSSError($name).'">';
    $lines[] = '<label for="'.$name.'">'.$value['label'];
    if (isset($value['require']) && $value['require'])
        $lines[] = ' <span class="red">*</span>';
    $lines[] = '</label>';
    $lines[] = getInput($name, $value);
    $lines[] = '<span id="'.$name.'-error" class="help-block'.showErrorMsg($name).'">' . $value['error'] . '</span>';
    $lines[] = '</div>';
    return implode('', $lines);
}

function getCheckbox($name, $value) {
    global $fields;
    $lines[] = '<div class="checkbox'.getCSSError($name).'">';
    $lines[] = '<label for="'.$name.'" style="color: black;">';
    $value['checked'] = !empty($_POST[$name]) && $_POST[$name] == $fields[$name]['value'];
    $lines[] = getInput($name, $value);
    $lines[] = $value['label'];
    if (isset($value['require']) && $value['require'])
        $lines[] = ' <span class="red">*</span>';
    $lines[] = '</label>';
    if (isset($value['error']))
      $lines[] = '<span id="'.$name.'-error" class="help-block'.showErrorMsg($name).'">' . $value['error'] . '</span>';
    $lines[] = '</div>';
    return implode('', $lines);
}

function getInput($name, $value) {
    $attr = [
        'type' => $value['type'],
        'id' => $name, 
        'name' => $name,
        'value' => getValue($name),
    ];
    if ($value['type'] != 'checkbox')
        $attr['class'] = 'form-control';
    if (isset($value['checked']) && $value['checked']) $attr['checked'] = 'checked';
    if (isset($value['require']) && $value['require']) $attr['required'] = 'required';
    if (isset($value['placeholder']) && $value['placeholder']) $attr['placeholder'] = $value['placeholder'];
    foreach ($attr as $key => $val) $lines[] = $key . '="' . $val . '"';
    return '<input ' . implode(' ', $lines) . '>';
}

function getValue($name) {
    global $fields;
    if (!empty($_POST[$name]))
        return $_POST[$name];
    if (!empty($fields[$name]['value']))
        return $fields[$name]['value'];
    return '';
}

if (isFormValid()) {
    saveToCSV('inscription.csv');
}

function saveToCSV($filename) {
    global $fields;
    if (!file_exists($filename)) {
        $keys = array_keys($fields);
        $head = implode(';', $keys);
        file_put_contents($filename, $head . PHP_EOL);
    }
    $data = postToData();
    $data = implode(';', $data);
    file_put_contents($filename, $data . PHP_EOL, FILE_APPEND);
}

function postToData(){
  global $fields;
  $values = [];
  foreach ($fields as $key => $value) {
    $values[] = isset($_POST[$key]) ? $_POST[$key] : '';
  }
  return $values;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulaire</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
      .red { color: red; }
    </style>
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container">
      <h1>Formulaire d'inscription</h1>
      <?php if (!isFormValid()) { ?>
      <form method="post" novalidate>
        <?php
        foreach ($fields as $name => $value) {
            if ($value['type'] == 'checkbox')
              echo getCheckbox($name, $value);
            else 
              echo getField($name, $value);
        }
        ?>
        <button type="submit" class="btn btn-primary">S'enregistrer</button>
        <button type="reset" class="btn btn-default">Annuler</button>
      </form>
      <?php } else { ?>
      MERCI !
      <?php } ?>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
    var form = document.querySelector('form');
    var elts = document.querySelectorAll('form input');

    var regex = {};
    <?php foreach ($fields as $key => $value) {
        if (!empty($value['regex']))
          echo "regex['".$key."'] = '".$value['regex']."';" . PHP_EOL . "\t";
    }?>
    
    form.addEventListener('submit', function(event) {
        // event.preventDefault();
        // if (formValid()) form.submit();
    });

    function formValid() {
        var valid = true;
        for (var i=0; i<elts.length; i++) {
            if (regex[elts[i].id] !== undefined) {
                var reg = new RegExp(regex[elts[i].id]);
                var fieldValid = reg.test(elts[i].value);
                if (!fieldValid) {
                    elts[i].parentNode.classList.add('has-error');
                    elts[i].nextElementSibling.classList.remove('hidden');
                }
                valid = fieldValid && valid;
            }
        }
        return valid;
    }

    elts.forEach(function(elt) {
        elt.addEventListener('focus', function(event) {
          if (elt.parentNode.classList.contains('has-error'))
            elt.parentNode.classList.remove('has-error');
          if (elt.nextElementSibling && elt.nextElementSibling.id.endsWith('-error') && !elt.nextElementSibling.classList.contains('hidden'))
            elt.nextElementSibling.classList.add('hidden');
        });
    });
    </script>
  </body>
</html>



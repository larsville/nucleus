<?php

// Extract the important values from the slash command.

$command = $_POST['command'];
$text = $_POST['text'];
$token = $_POST['token'];

// Make sure we have the proper token.

$tokenRequired = ConfigValue("auth-token");

if ($token != $tokenRequired) // token from slash command config page
{
  $msg = "fail";
  $msg = ConfigValue("msg-auth-failed");
  exit($msg);
  echo $msg;
}

echo Response($text);

/////////////////

function Debug($scope, $name, $value)
{
  echo "\n-->".$scope.": <".$name."> = <".$value.">";
}

/////////////////

function ConfigValue($key)
{
  static $configValues;
  if ($configValues == NULL)
  {
  	$configValues = parse_ini_file("config.ini.php");
	Debug("ConfigValue: after parse_ini_file", "key", $key);
  	print_r($configValues);
  }
  Debug("ConfigValue: before return", "key", $key);
  print_r($configValues);
  return $configValues[$key];
}

/////////////////

// Return a response for the given input, including help or failure messages.

function Response($input)
{
  $result = "";
  $input = strtolower($input);

  if ($input == "?" or $input == "" or $input == "help")
  {
    // Here if the user seems to need help.
    $result = ConfigValue("msg-help");
  }
  else
  {
    // Get the definition for our term.
    $result = Lookup($input);
    Debug("Response", "input", $input);

    if ($result == "")
    {
      // If there is no definition, admit defeat and put up a help message.
      //$quote = '"';
      $result = str_ireplace(ConfigValue("msg-term-token"), $input, ConfigValue("msg-help"));
    }
  }

  return $result;

} // end Response

/////////////////

// Return whatever our dataset contains for the given term.

function Lookup($term)
{
  $result = "";
  $termWithSep = strtolower($term).chr(9); // append tab char
    Debug("Lookup", "termWithSep", $termWithSep);

  static $DataLines;
  if ($DataLines == NULL)
  {
    $DataLines = file(ConfigValue("data-file-name"));
    sort($DataLines);
  }

  foreach($DataLines as $line)
  {
    //if strlen((trim($line)) >= 0)
    {
      Debug("Lookup", "stripos($line, $termWithSep)", stripos($line, $termWithSep));
      if (stripos($line, $termWithSep) === 0) // note strict comparison operator
      {
        $result = substr($line, strlen($termWithSep));
        $result = str_ireplace("\\n", chr(13), $result);
        break; // one match is all we need
        // hack; should accumulate all matches in an array
      }
    }
  }

  Debug("Lookup", "result", $result);
  return $result;

} // end Lookup

?>

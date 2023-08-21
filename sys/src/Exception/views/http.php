<!DOCTYPE html>
<html>
  <head>
    <title><?=$msg?></title>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@100&display=swap" rel="stylesheet">
    <style>
        .box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 90vh;
        }

        h1 {
            margin: 0;
            font: 12em 'Work Sans', sans-serif;
            color: grey;
        }

        p {
            font-family: 'Work Sans', sans-serif;
            font-size: 2.5em;
            font-weight: 600;
            color: grey;
        }
    </style>
  </head>
  <body class="box">

        <h1><?=$code?></h1>
        <p><?=$msg?></p>

  </body>
</html>



<?php

/* home.html */
class __TwigTemplate_82566e51283cc3c1adaa31c7b626c3d1 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!doctype html>
<html>
\t<head>
\t\t<meta charset=\"utf-8\">
\t\t<title>Sample app</title>
\t</head>
\t<body>
\t\t<h1>Hello World</h1>
\t\t<p>This is a sample app using minibase.</p>
\t</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "home.html";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }
}

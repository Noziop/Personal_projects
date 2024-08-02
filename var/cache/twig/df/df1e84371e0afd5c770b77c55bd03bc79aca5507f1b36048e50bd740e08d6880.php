<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* layout.twig */
class __TwigTemplate_0d0b7f3bd184fd97482e69c68b7a08f442406fa3226a860337211ea861f4c5b9 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'header_title' => [$this, 'block_header_title'],
            'content' => [$this, 'block_content'],
            'javascripts' => [$this, 'block_javascripts'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>";
        // line 6
        yield from $this->unwrap()->yieldBlock('title', $context, $blocks);
        yield "</title>
    <link rel=\"stylesheet\" href=\"/assets/css/style.css\">
\t<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css\">
\t<link rel=\"stylesheet\" type=\"text/css\" href=\"https://npmcdn.com/flatpickr/dist/themes/material_red.css\">
\t<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/favicon.png\">
</head>
<body>
<header class=\"neumorphic-header\">
    <h1 class=\"neumorphic-title\">";
        // line 14
        yield from $this->unwrap()->yieldBlock('header_title', $context, $blocks);
        yield "</h1>
    <nav>
        <ul>
            <li><a href=\"";
        // line 17
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("home"), "html", null, true);
        yield "\">Accueil</a></li>
            <li><a href=\"";
        // line 18
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("tirage.index"), "html", null, true);
        yield "\">Tirage au sort</a></li>
            <li><a href=\"";
        // line 19
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("configuration.index"), "html", null, true);
        yield "\">Configuration</a></li>
\t\t\t<li><a href=\"";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("tableau_de_bord"), "html", null, true);
        yield "\">Tableau de bord</a></li>
        </ul>
    </nav>
</header>


    <main>
        ";
        // line 27
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 28
        yield "    </main>

    <footer>
        <p>&copy; ";
        // line 31
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y"), "html", null, true);
        yield " - Fassih Belmokhtar - Speaker Of The Day. Tous droits réservés.</p>
    </footer>

    ";
        // line 34
        yield from $this->unwrap()->yieldBlock('javascripts', $context, $blocks);
        // line 43
        yield "
</body>
</html>
";
        return; yield '';
    }

    // line 6
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Speaker Of The Day";
        return; yield '';
    }

    // line 14
    public function block_header_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    // line 27
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    // line 34
    public function block_javascripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "\t<script src=\"https://cdn.jsdelivr.net/npm/flatpickr\"></script>
\t<script type=\"module\" src=\"/assets/js/cohortManager.js\"></script>
\t<script type=\"module\" src=\"/assets/js/studentAPI.js\"></script>
\t<script type=\"module\" src=\"/assets/js/studentForm.js\"></script>
\t<script type=\"module\" src=\"/assets/js/StudentList.js\"></script>
\t<script type=\"module\" src=\"/assets/js/studentManager.js\"></script>
\t<script type=\"module\" src=\"/assets/js/main.js\"></script>
\t";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "layout.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  133 => 34,  126 => 27,  119 => 14,  111 => 6,  103 => 43,  101 => 34,  95 => 31,  90 => 28,  88 => 27,  78 => 20,  74 => 19,  70 => 18,  66 => 17,  60 => 14,  49 => 6,  42 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{% block title %}Speaker Of The Day{% endblock %}</title>
    <link rel=\"stylesheet\" href=\"/assets/css/style.css\">
\t<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css\">
\t<link rel=\"stylesheet\" type=\"text/css\" href=\"https://npmcdn.com/flatpickr/dist/themes/material_red.css\">
\t<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/favicon.png\">
</head>
<body>
<header class=\"neumorphic-header\">
    <h1 class=\"neumorphic-title\">{% block header_title %}{% endblock %}</h1>
    <nav>
        <ul>
            <li><a href=\"{{ url_for('home') }}\">Accueil</a></li>
            <li><a href=\"{{ url_for('tirage.index') }}\">Tirage au sort</a></li>
            <li><a href=\"{{ url_for('configuration.index') }}\">Configuration</a></li>
\t\t\t<li><a href=\"{{ url_for('tableau_de_bord') }}\">Tableau de bord</a></li>
        </ul>
    </nav>
</header>


    <main>
        {% block content %}{% endblock %}
    </main>

    <footer>
        <p>&copy; {{ \"now\"|date(\"Y\") }} - Fassih Belmokhtar - Speaker Of The Day. Tous droits réservés.</p>
    </footer>

    {% block javascripts %}
\t<script src=\"https://cdn.jsdelivr.net/npm/flatpickr\"></script>
\t<script type=\"module\" src=\"/assets/js/cohortManager.js\"></script>
\t<script type=\"module\" src=\"/assets/js/studentAPI.js\"></script>
\t<script type=\"module\" src=\"/assets/js/studentForm.js\"></script>
\t<script type=\"module\" src=\"/assets/js/StudentList.js\"></script>
\t<script type=\"module\" src=\"/assets/js/studentManager.js\"></script>
\t<script type=\"module\" src=\"/assets/js/main.js\"></script>
\t{% endblock %}

</body>
</html>
", "layout.twig", "/volume1/web/Personal_projects/Tirage_au_sort/webapp/templates/layout.twig");
    }
}

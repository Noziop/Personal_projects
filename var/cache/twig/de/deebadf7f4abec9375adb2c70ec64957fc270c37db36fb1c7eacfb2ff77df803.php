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
class __TwigTemplate_10bf4f51fac0c097d981eded528f73f4f35130dbb5cf0b9327cba3560dfa421f extends Template
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
    <link rel=\"stylesheet\" href=\"/assets/css/styles.css\">
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"https://npmcdn.com/flatpickr/dist/themes/material_red.css\">
    <link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/favicon.png\">
</head>
<body>
<header class=\"neumorphic-header\">
    <h1 class=\"neumorphic-title\">";
        // line 14
        yield from $this->unwrap()->yieldBlock('header_title', $context, $blocks);
        yield "</h1>
\t<nav>
\t\t<ul>
\t\t\t<li><a href=\"";
        // line 17
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("home"), "html", null, true);
        yield "\">Accueil</a></li>
\t\t\t";
        // line 18
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["session"] ?? null), "user", [], "any", false, false, false, 18)) {
            // line 19
            yield "\t\t\t\t<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("auth.logout"), "html", null, true);
            yield "\">Déconnexion</a></li>
\t\t\t";
        } else {
            // line 21
            yield "\t\t\t\t<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("auth.loginPage"), "html", null, true);
            yield "\">Connexion</a></li>
\t\t\t";
        }
        // line 23
        yield "\t\t</ul>
\t</nav>
</header>

<main>
    ";
        // line 28
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 29
        yield "</main>

<footer>
    <p>&copy; ";
        // line 32
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y"), "html", null, true);
        yield " - Fassih Belmokhtar - Speaker Of The Day. Tous droits réservés.</p>
</footer>

";
        // line 35
        yield from $this->unwrap()->yieldBlock('javascripts', $context, $blocks);
        // line 38
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

    // line 28
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    // line 35
    public function block_javascripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "<script src=\"https://cdn.jsdelivr.net/npm/flatpickr\"></script>
";
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
        return array (  136 => 35,  129 => 28,  122 => 14,  114 => 6,  106 => 38,  104 => 35,  98 => 32,  93 => 29,  91 => 28,  84 => 23,  78 => 21,  72 => 19,  70 => 18,  66 => 17,  60 => 14,  49 => 6,  42 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{% block title %}Speaker Of The Day{% endblock %}</title>
    <link rel=\"stylesheet\" href=\"/assets/css/styles.css\">
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"https://npmcdn.com/flatpickr/dist/themes/material_red.css\">
    <link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/favicon.png\">
</head>
<body>
<header class=\"neumorphic-header\">
    <h1 class=\"neumorphic-title\">{% block header_title %}{% endblock %}</h1>
\t<nav>
\t\t<ul>
\t\t\t<li><a href=\"{{ url_for('home') }}\">Accueil</a></li>
\t\t\t{% if session.user %}
\t\t\t\t<li><a href=\"{{ url_for('auth.logout') }}\">Déconnexion</a></li>
\t\t\t{% else %}
\t\t\t\t<li><a href=\"{{ url_for('auth.loginPage') }}\">Connexion</a></li>
\t\t\t{% endif %}
\t\t</ul>
\t</nav>
</header>

<main>
    {% block content %}{% endblock %}
</main>

<footer>
    <p>&copy; {{ \"now\"|date(\"Y\") }} - Fassih Belmokhtar - Speaker Of The Day. Tous droits réservés.</p>
</footer>

{% block javascripts %}
<script src=\"https://cdn.jsdelivr.net/npm/flatpickr\"></script>
{% endblock %}

</body>
</html>
", "layout.twig", "/volume1/web/Personal_projects/Tirage_au_sort/webapp/templates/layout.twig");
    }
}

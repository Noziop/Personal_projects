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

/* error.twig */
class __TwigTemplate_dba68e4c70d29dd2b2d93f63260ae3dfc8c428e5422a792a9644259a328855df extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'header_title' => [$this, 'block_header_title'],
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return "layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $this->parent = $this->loadTemplate("layout.twig", "error.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Erreur ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["statusCode"] ?? null), "html", null, true);
        return; yield '';
    }

    // line 5
    public function block_header_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Erreur ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["statusCode"] ?? null), "html", null, true);
        yield " ¯\\_(ツ)_/¯";
        return; yield '';
    }

    // line 7
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 8
        yield "<div class=\"neumorphic-container\">
    <h1 class=\"neumorphic-title-red\">Erreur ";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["statusCode"] ?? null), "html", null, true);
        yield " ¯\\_(ツ)_/¯</h1>
    <p>";
        // line 10
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["errorMessage"] ?? null), "html", null, true);
        yield "</p>
    <a href=\"";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("home"), "html", null, true);
        yield "\" class=\"neumorphic-button\">Retour à l'accueil</a>
</div>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "error.twig";
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
        return array (  83 => 11,  79 => 10,  75 => 9,  72 => 8,  68 => 7,  58 => 5,  49 => 3,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends \"layout.twig\" %}

{% block title %}Erreur {{ statusCode }}{% endblock %}

{% block header_title %}Erreur {{ statusCode }} ¯\\_(ツ)_/¯{% endblock %}

{% block content %}
<div class=\"neumorphic-container\">
    <h1 class=\"neumorphic-title-red\">Erreur {{ statusCode }} ¯\\_(ツ)_/¯</h1>
    <p>{{ errorMessage }}</p>
    <a href=\"{{ url_for('home') }}\" class=\"neumorphic-button\">Retour à l'accueil</a>
</div>
{% endblock %}
", "error.twig", "/volume1/web/Personal_projects/Tirage_au_sort/webapp/templates/error.twig");
    }
}

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

/* dashboard.twig */
class __TwigTemplate_1d3851f7bcaf6083f2ccff5fc3b1f23abfbdc33bdecf241c5ed72fbc75d3734b extends Template
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
        $this->parent = $this->loadTemplate("layout.twig", "dashboard.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Dashboard - ";
        yield from $this->yieldParentBlock("title", $context, $blocks);
        return; yield '';
    }

    // line 5
    public function block_header_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Dashboard";
        return; yield '';
    }

    // line 7
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 8
        yield "<div class=\"neumorphic-container\">
    <h2 class=\"neumorphic-title-red\">Welcome, ";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["user"] ?? null), "username", [], "any", false, false, false, 9), "html", null, true);
        yield "</h2>
    <p>This is your dashboard. Here you can manage your account and access various features.</p>
    <a href=\"";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("auth.logout"), "html", null, true);
        yield "\" class=\"neumorphic-button\">Logout</a>
</div>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "dashboard.twig";
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
        return array (  78 => 11,  73 => 9,  70 => 8,  66 => 7,  58 => 5,  49 => 3,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends \"layout.twig\" %}

{% block title %}Dashboard - {{ parent() }}{% endblock %}

{% block header_title %}Dashboard{% endblock %}

{% block content %}
<div class=\"neumorphic-container\">
    <h2 class=\"neumorphic-title-red\">Welcome, {{ user.username }}</h2>
    <p>This is your dashboard. Here you can manage your account and access various features.</p>
    <a href=\"{{ url_for('auth.logout') }}\" class=\"neumorphic-button\">Logout</a>
</div>
{% endblock %}
", "dashboard.twig", "/volume1/web/Personal_projects/Tirage_au_sort/webapp/templates/dashboard.twig");
    }
}

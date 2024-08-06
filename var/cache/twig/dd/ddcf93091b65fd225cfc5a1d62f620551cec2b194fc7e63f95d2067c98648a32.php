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

/* error/500.twig */
class __TwigTemplate_bb3077cc87e90bdd21898cc29eb4f349f6199a346d3cc3590370c8d63394119b extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'title' => [$this, 'block_title'],
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
        $this->parent = $this->loadTemplate("layout.twig", "error/500.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Error 500 - Internal Server Error";
        return; yield '';
    }

    // line 5
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 6
        yield "    <h1>500 - Internal Server Error</h1>
    <p>Oops! Something went wrong on our end. We're working to fix it.</p>
    ";
        // line 8
        if (($context["displayErrorDetails"] ?? null)) {
            // line 9
            yield "        <h2>Error Details:</h2>
        <pre>";
            // line 10
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["exception"] ?? null), "getMessage", [], "method", false, false, false, 10), "html", null, true);
            yield "</pre>
    ";
        }
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "error/500.twig";
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
        return array (  69 => 10,  66 => 9,  64 => 8,  60 => 6,  56 => 5,  48 => 3,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends \"layout.twig\" %}

{% block title %}Error 500 - Internal Server Error{% endblock %}

{% block content %}
    <h1>500 - Internal Server Error</h1>
    <p>Oops! Something went wrong on our end. We're working to fix it.</p>
    {% if displayErrorDetails %}
        <h2>Error Details:</h2>
        <pre>{{ exception.getMessage() }}</pre>
    {% endif %}
{% endblock %}", "error/500.twig", "/volume1/web/Personal_projects/Tirage_au_sort/webapp/templates/error/500.twig");
    }
}

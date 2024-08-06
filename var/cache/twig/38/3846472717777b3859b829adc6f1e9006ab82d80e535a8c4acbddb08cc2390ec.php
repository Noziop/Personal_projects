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

/* auth/login.twig */
class __TwigTemplate_16f229da1c39e96c50cdba72a390dbd3f532da8d6c816a1b802cf6822f50d578 extends Template
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
        // line 8
        return "layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $this->parent = $this->loadTemplate("layout.twig", "auth/login.twig", 8);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 10
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Welcome - Holberton Thonon Ritual Manager";
        return; yield '';
    }

    // line 12
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 13
        yield "<div class=\"neumorphic-container\">
    <h1 class=\"neumorphic-title\">Welcome to Holberton Thonon: Ritual Manager</h1>
    
    <div class=\"app-description\">
        <p>Streamline your Holberton School rituals with our comprehensive management tool. 
        Efficiently organize Speaker of the Day, manage cohorts, and track student participation.</p>
    </div>

    <div class=\"login-section\">
        <h2 class=\"neumorphic-title-red\">Login</h2>
        ";
        // line 23
        if (($context["error"] ?? null)) {
            // line 24
            yield "            <p class=\"error\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error"] ?? null), "html", null, true);
            yield "</p>
        ";
        }
        // line 26
        yield "        <form action=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Slim\Views\TwigRuntimeExtension')->urlFor("auth.login"), "html", null, true);
        yield "\" method=\"post\" class=\"neumorphic-form\">
            <div class=\"neumorphic-input-group\">
                <label for=\"username\" class=\"neumorphic-label\">Username:</label>
                <input type=\"text\" id=\"username\" name=\"username\" required class=\"neumorphic-input\">
            </div>
            <div class=\"neumorphic-input-group\">
                <label for=\"password\" class=\"neumorphic-label\">Password:</label>
                <input type=\"password\" id=\"password\" name=\"password\" required class=\"neumorphic-input\">
            </div>
            <button type=\"submit\" class=\"neumorphic-button\">Login</button>
        </form>
    </div>
</div>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "auth/login.twig";
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
        return array (  80 => 26,  74 => 24,  72 => 23,  60 => 13,  56 => 12,  48 => 10,  37 => 8,);
    }

    public function getSourceContext()
    {
        return new Source("{# 
 # File: templates/auth/login.twig
 # 
 # This template serves as the home page and login page for the Holberton Thonon Ritual Manager.
 # It displays a welcome message, a brief description of the app, and a login form.
 #}

{% extends \"layout.twig\" %}

{% block title %}Welcome - Holberton Thonon Ritual Manager{% endblock %}

{% block content %}
<div class=\"neumorphic-container\">
    <h1 class=\"neumorphic-title\">Welcome to Holberton Thonon: Ritual Manager</h1>
    
    <div class=\"app-description\">
        <p>Streamline your Holberton School rituals with our comprehensive management tool. 
        Efficiently organize Speaker of the Day, manage cohorts, and track student participation.</p>
    </div>

    <div class=\"login-section\">
        <h2 class=\"neumorphic-title-red\">Login</h2>
        {% if error %}
            <p class=\"error\">{{ error }}</p>
        {% endif %}
        <form action=\"{{ url_for('auth.login') }}\" method=\"post\" class=\"neumorphic-form\">
            <div class=\"neumorphic-input-group\">
                <label for=\"username\" class=\"neumorphic-label\">Username:</label>
                <input type=\"text\" id=\"username\" name=\"username\" required class=\"neumorphic-input\">
            </div>
            <div class=\"neumorphic-input-group\">
                <label for=\"password\" class=\"neumorphic-label\">Password:</label>
                <input type=\"password\" id=\"password\" name=\"password\" required class=\"neumorphic-input\">
            </div>
            <button type=\"submit\" class=\"neumorphic-button\">Login</button>
        </form>
    </div>
</div>
{% endblock %}
", "auth/login.twig", "/volume1/web/Personal_projects/Tirage_au_sort/webapp/templates/auth/login.twig");
    }
}

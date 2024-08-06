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

/* home.twig */
class __TwigTemplate_7b86b8525b3904eb3a74ade1032fdb9699525d4b27aef1f7ca9d494d9a61b9a7 extends Template
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
        $this->parent = $this->loadTemplate("layout.twig", "home.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Holberton Rituals Manager";
        return; yield '';
    }

    // line 5
    public function block_header_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "Holberton Rituals Manager";
        return; yield '';
    }

    // line 7
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        yield "<div class=\"neumorphic-container\">
    <h2 class=\"neumorphic-title\">Bienvenue sur l'Application de gestion des rituels d'Holberton Thonon-les-Bains</h2>
    
    <div class=\"neumorphic-card\">
        <h3>À propos de l'application</h3>
        <p>Cette application a été conçue pour faciliter l'organisation des différents rituels à Holberton School Thonon.</p>
        
        <h3>Fonctionnalités principales :</h3>
        <ul>
            <li>Tirage au sort automatisé des élèves pour les présentations S.O.D.</li>
            <li>Gestion des cohortes et de leurs calendriers spécifiques</li>
            <li>Prise en compte des vacances et jours fériés</li>
            <li>Gestion des indisponibilités individuelles des élèves</li>
        </ul>
        
        <h3>Comment ça marche :</h3>
        <ol>
            <li>Configurez les cohortes et leurs jours de tirage autorisés</li>
            <li>Ajoutez les périodes de vacances pour chaque cohorte</li>
            <li>Enregistrez les contraintes de calendrier (jours fériés, événements spéciaux)</li>
            <li>Lancez un tirage au sort en sélectionnant les cohortes concernées et la date de début</li>
            <li>Obtenez un planning de passages optimisé pour le S.O.D.</li>
        </ol>
        
        <p>Utilisez le menu de navigation pour accéder aux différentes fonctionnalités de l'application.</p>
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
        return "home.twig";
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
        return array (  65 => 7,  57 => 5,  49 => 3,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends \"layout.twig\" %}

{% block title %}Holberton Rituals Manager{% endblock %}

{% block header_title %}Holberton Rituals Manager{% endblock %}

{% block content %}
<div class=\"neumorphic-container\">
    <h2 class=\"neumorphic-title\">Bienvenue sur l'Application de gestion des rituels d'Holberton Thonon-les-Bains</h2>
    
    <div class=\"neumorphic-card\">
        <h3>À propos de l'application</h3>
        <p>Cette application a été conçue pour faciliter l'organisation des différents rituels à Holberton School Thonon.</p>
        
        <h3>Fonctionnalités principales :</h3>
        <ul>
            <li>Tirage au sort automatisé des élèves pour les présentations S.O.D.</li>
            <li>Gestion des cohortes et de leurs calendriers spécifiques</li>
            <li>Prise en compte des vacances et jours fériés</li>
            <li>Gestion des indisponibilités individuelles des élèves</li>
        </ul>
        
        <h3>Comment ça marche :</h3>
        <ol>
            <li>Configurez les cohortes et leurs jours de tirage autorisés</li>
            <li>Ajoutez les périodes de vacances pour chaque cohorte</li>
            <li>Enregistrez les contraintes de calendrier (jours fériés, événements spéciaux)</li>
            <li>Lancez un tirage au sort en sélectionnant les cohortes concernées et la date de début</li>
            <li>Obtenez un planning de passages optimisé pour le S.O.D.</li>
        </ol>
        
        <p>Utilisez le menu de navigation pour accéder aux différentes fonctionnalités de l'application.</p>
    </div>
</div>
{% endblock %}
", "home.twig", "/volume1/web/Personal_projects/Tirage_au_sort/webapp/templates/home.twig");
    }
}

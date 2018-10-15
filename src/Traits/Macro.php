<?php

namespace Lukesnowden\RelationshipMacros\Traits;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;

trait Macro
{

    /**
     * @var array
     */
    protected static $relationshipMacros = [];

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue( $key )
    {
        if( $response = parent::getRelationValue( $key ) ) {
            return $response;
        }

        if( isset( static::$relationshipMacros[ $key ] ) ) {
            return $this->loadRelationshipMacro( $key );
        }
    }

    /**
     * @param $method
     * @param Closure $closure
     * @return void
     */
    public static function relationshipMacro( $method, Closure $closure )
    {
        self::$relationshipMacros[ $method ] = $closure;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call( $method, $parameters )
    {
        if( isset( static::$relationshipMacros[ $method ] ) ) {
            return $this->loadRelationshipMacro( $method, false );
        }
        return parent::__call( $method, $parameters );
    }

    /**
     * @param string $method
     * @param bool $getResults
     * @return mixed
     */
    protected function loadRelationshipMacro( $method, $getResults = true )
    {
        $macro = static::$relationshipMacros[ $method ];
        if( $macro instanceof Closure ) {
            $relations = call_user_func_array( $macro->bindTo( $this, static::class ), [ $this->query() ] );
            if( $getResults ) {
                $this->setRelation( $method, $results = $relations->getResults() );
                return $results;
            }
            return $relations;
        }
    }

    /**
     * @param $class
     * @throws \ReflectionException
     * @return void
     */
    public static function relationshipMacros( $class )
    {
        $methods = ( new ReflectionClass( $class ) )->getMethods( ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED );
        foreach( $methods as $method ) {
            $method->setAccessible( true );
            self::relationshipMacro( $method->name, $method->invoke( $class ) );
        }
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        $builder = $this->newQueryWithoutScopes();
        foreach ($this->getGlobalScopes() as $identifier => $scope) {
            $builder->withGlobalScope($identifier, $scope);
        }
        $this->applyNewScopes( $builder );
        return $builder;
    }

    /**
     * @param $builder
     */
    protected function applyNewScopes( $builder )
    {
        foreach( self::$relationshipMacros as $name => $closure ) {
            if( Str::startsWith( $name, 'scope' ) ) {
                $builder->macro( lcfirst( Str::replaceFirst( 'scope', '', $name ) ), $closure );
            }
        }
    }

}

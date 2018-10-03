<?php

namespace Lukesnowden\RelationshipMacros\Traits;

use Closure;
use ReflectionClass;
use ReflectionMethod;

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
        if( in_array( $method, [ 'increment', 'decrement' ] ) ) {
            return call_user_func_array( [ $this, $method ], $parameters );
        }
        $query = $this->newQuery();
        if( method_exists( $query, $method ) ) {
            return call_user_func_array( [ $query, $method ], $parameters );
        }
        if( isset( static::$relationshipMacros[ $method ] ) ) {
            return $this->loadRelationshipMacro( $method, false );
        }
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
            $relations = call_user_func_array( $macro->bindTo( $this, static::class ), [] );
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

}

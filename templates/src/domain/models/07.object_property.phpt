	/**
	 * @ORM\ManyToOne(
	 *     targetEntity="\%namespace%\Domain\Models\%property_classname%",
	 *     fetch="EAGER",
	 *     inversedBy="%inversed_by_property_name%"
	 * )
	 * @ORM\JoinColumn(
	 *     name="%object_property_snakecase%_id",
	 *     referencedColumnName="id"
	 * )
	 *
	 * @var \%namespace%\Domain\Models\%property_classname%
	 */
	public $%object_property_camelcase%;


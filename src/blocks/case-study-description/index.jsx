import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'sparkagency/case-study-description-item' ];

const DEFAULT_TEMPLATE = [
	[
		'sparkagency/case-study-description-item',
		{ title: 'Kampania multi-market' },
		[
			[ 'core/paragraph', { content: 'Jedna idea. 28 rynków.', className: 'is-case-study-lede' } ],
		],
	],
	[
		'sparkagency/case-study-description-item',
		{ title: 'Otwarcie' },
		[
			[ 'core/paragraph', { content: 'Perfect Nonsense wymagało czegoś więcej niż przygotowania centralnego mastera. Kampania miała zachować swój charakter w wielu formatach i lokalnych wersjach, dlatego produkcja oraz adaptacje były uwzględnione już podczas rozwijania koncepcji.' } ],
		],
	],
	[
		'sparkagency/case-study-description-item',
		{ title: 'Wyzwanie' },
		[
			[ 'core/paragraph', { content: 'Jedna kampania miała trafić na 28 rynków digitalowych. Każdy potrzebował właściwych formatów i lokalnej wersji, ale wszystkie materiały powinny nadal wyglądać i brzmieć jak ta sama marka.' } ],
			[ 'core/paragraph', { content: 'Przy takiej skali każda późna zmiana mastera może oznaczać konieczność poprawienia wielu materiałów. Dlatego sposób pracy miał równie duże znaczenie jak sama kreacja.' } ],
		],
	],
	[
		'sparkagency/case-study-description-item',
		{ title: 'Co zrobiliśmy' },
		[
			[ 'core/paragraph', { content: 'Strategię, kreację, produkcję, postprodukcję i lokalne adaptacje prowadzono jako elementy jednego procesu.' } ],
			[ 'core/paragraph', { content: 'Master stał się punktem wyjścia dla kolejnych formatów i wersji. Zespół koordynował zmiany oraz rollout tak, aby lokalne materiały zachowywały wspólne założenia kampanii.' } ],
		],
	],
	[
		'sparkagency/case-study-description-item',
		{ title: 'Rola Spark Warsaw' },
		[
			[ 'core/paragraph', { content: 'Warsaw odpowiadał za prowadzenie kampanii jako projektu multi-market, łącząc pracę nad centralnym kierunkiem z późniejszym przygotowaniem lokalnych wersji.' } ],
		],
	],
	[
		'sparkagency/case-study-description-item',
		{ title: 'Skala' },
		[
			[
				'core/columns',
				{ className: 'spark-case-study-description__metrics' },
				[
					[
						'core/column',
						{},
						[
							[ 'core/heading', { level: 3, content: '28' } ],
							[ 'core/paragraph', { content: 'rynków digitalowych', className: 'is-case-study-muted' } ],
						],
					],
					[
						'core/column',
						{},
						[
							[ 'core/heading', { level: 3, content: '1' } ],
							[ 'core/paragraph', { content: 'centralny kierunek komunikacji rozwinięty dla wielu lokalnych odbiorców i formatów', className: 'is-case-study-muted' } ],
						],
					],
				],
			],
		],
	],
];

const Edit = () => {
	const blockProps = useBlockProps( { className: 'spark-case-study-description' } );
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		allowedBlocks: ALLOWED_BLOCKS,
		template: DEFAULT_TEMPLATE,
		templateLock: false,
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return <section { ...innerBlocksProps } />;
};

registerBlockType( 'sparkagency/case-study-description', {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
} );

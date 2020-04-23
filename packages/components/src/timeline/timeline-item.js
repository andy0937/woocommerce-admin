/**
 * External dependencies
 */
import classnames from 'classnames';
import moment from 'moment';
import PropTypes from 'prop-types';

const TimelineItem = ( props ) => {
	const { item, className } = props;

	const itemClassName = classnames( 'woocommerce-timeline-item', className );
	const itemTimeString = moment.unix( item.datetime ).format( 'h:mma' );

	return (
		<li className={ itemClassName }>
			<div className={ 'woocommerce-timeline-item__top-border' }></div>
			<div className={ 'woocommerce-timeline-item__title' }>
				<div className={ 'woocommerce-timeline-item__headline' }>
					{ item.icon }
					<span>{ item.headline }</span>
				</div>
				<span className={ 'woocommerce-timeline-item__timestamp' }>
					{ item.hideTimestamp || false ? null : itemTimeString }
				</span>
			</div>
			<div className={ 'woocommerce-timeline-item__body' }>
				{ ( item.body || [] ).map( ( bodyItem, index ) => (
					<span key={ `timeline-item-body-${ index }` }>
						{ bodyItem }
					</span>
				) ) }
			</div>
		</li>
	);
};

TimelineItem.propTypes = {
	/**
	 * Additional CSS classes.
	 */
	className: PropTypes.string,
	/**
	 * An array of list items.
	 */
	item: PropTypes.shape( {
		/**
		 * Timestamp (in seconds) for the timeline item.
		 */
		datetime: PropTypes.number.isRequired,
		/**
		 * Icon for the Timeline item.
		 */
		icon: PropTypes.element.isRequired,
		/**
		 * Headline displayed for the list item.
		 */
		headline: PropTypes.oneOfType( [ PropTypes.element, PropTypes.string ] )
			.isRequired,
		/**
		 * Body displayed for the list item.
		 */
		body: PropTypes.arrayOf(
			PropTypes.oneOfType( [ PropTypes.element, PropTypes.string ] )
		),
		/**
		 * Allows users to toggle the timestamp on or off.
		 */
		hideTimestamp: PropTypes.bool,
	} ).isRequired,
};

TimelineItem.defaultProps = {
	className: '',
	item: {},
};

export default TimelineItem;
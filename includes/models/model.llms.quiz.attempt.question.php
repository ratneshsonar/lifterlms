<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Quiz Attempt Answer Question
 * @since   3.16.0
 * @version [version]
 */
class LLMS_Quiz_Attempt_Question {

	private $data = array();

	/**
	 * Constructor
	 * @param    array      $data   question data array from attempt record
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function __construct( $data = array() ) {

		$this->data = wp_parse_args( $data, array(
			'id' => null,
			'earned' => null,
			'points' => null,
			'remarks' => null,
			'answer' => null,
			'correct' => null,
		) );

	}

	/**
	 * Determine if it's possible to manually grade the question
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	public function can_be_manually_graded() {

		$question = $this->get_question();

		// the question is auto-gradeable so it cannot be manually graded
		if ( $question->get_auto_grade_type() ) {
			return false;
		} elseif ( $question->supports( 'grading', 'manual' ) || $question->supports( 'grading', 'conditional' ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Getter
	 * @param    string     $key      data key name
	 * @param    mixed      $default  default fallback value if key is unset
	 * @return   mixed
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get( $key, $default = '' ) {
		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}
		return $default;
	}

	/**
	 * Retrieve anwser HTML for the question
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_answer() {

		$ret = '';
		$question = $this->get_question();
		$answers = $this->get( 'answer' );

		if ( $answers ) {

			if ( $question->supports( 'choices' ) && $question->supports( 'grading', 'auto' ) ) {

				foreach ( $answers as $aid ) {

					$choice = $question->get_choice( $aid );
					$ret .= $choice->get_choice();

				}
			} else {

				$ret = implode( ', ', array_map( 'wp_kses_post', $answers ) );

			}
		}

		return $ret;

	}

	/**
	 * Retrieve an instance of the LLMS_Question
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_question() {
		return llms_get_post( $this->get( 'id' ) );
	}

	/**
	 * Retrieve the status icon HTML based on the question's status/answer
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_status_icon() {

		$icon = '';

		switch ( $this->get_status() ) {

			case 'graded':
				if ( $this->is_correct() ) {
					$icon = 'check';
					$tip = esc_attr__( 'Correct answer', 'lifterlms' );
				} else {
					$icon = 'times';
					$tip = esc_attr__( 'Incorrect answer', 'lifterlms' );
				}
			break;
			case 'waiting':
				$icon = 'clock-o';
				$tip = esc_attr__( 'Awaiting review', 'lifterlms' );
			break;

		}

		if ( $icon ) {
			return sprintf( '<span class="llms-status-icon-tip tip--top-left" data-tip="%1$s"><i class="llms-status-icon fa fa-%2$s"></i><span>', $tip, $icon );
		}

		return '';

	}

	/**
	 * Receive the graded status of the question
	 * @return   string      [graded|waiting|none]
	 * @since    3.16.0
	 * @version  [version]
	 */
	public function get_status() {
		$question = $this->get_question();
		if ( $question->get_auto_grade_type() ) {
			return 'graded';
		} elseif ( $question->supports( 'grading', 'manual' ) || $question->supports( 'grading', 'conditional' ) ) {
			if ( ! $this->get( 'correct' ) ) {
				return 'waiting';
			} else {
				return 'graded';
			}
		}
		return 'none';
	}

	/**
	 * Determine if remarks are available for the question
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function has_remarks() {

		return ( $this->get( 'remarks' ) );

	}

	/**
	 * Detemrine if a question is correct
	 * @return   bool
	 * @since    [version]
	 * @version  [version]
	 */
	public function is_correct() {

		if ( 'graded' === $this->get_status() ) {
			return llms_parse_bool( $this->get( 'correct' ) );
		}

		return false;

	}

	/**
	 * Setter
	 * @param    string    $key  data key name
	 * @param    mixed     $val  value
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function set( $key, $val ) {
		$this->data[ $key ] = $val;
	}

}

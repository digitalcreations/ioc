require 'spec_helper'

describe 'puppi::rollback' do

  let(:title) { 'puppi::rollback' }
  let(:node) { 'rspec.example42.com' }
  let(:params) {
    { 'enable'   =>  'true',
      'name'     =>  'get',
      'command'  =>  'echo',
      'priority' =>  '50',
      'project'  =>  'myapp',
    }
  }

  describe 'Test puppi rollback step file creation' do
    it 'should create a puppi::rollback step file' do
      should contain_file('/etc/puppi/projects/myapp/rollback/50-get').with_ensure('present')
    end
    it 'should populate correctly the puppi::rollback step file' do
      should contain_file('/etc/puppi/projects/myapp/rollback/50-get').with_content("su - root -c \"export project=myapp && /etc/puppi/scripts/echo \"\n")
    end
  end

end
